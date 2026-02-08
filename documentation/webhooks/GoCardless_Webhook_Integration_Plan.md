# GoCardless Webhook Integration Plan

## Overview

Implement webhook handling for GoCardless payment events in the ka-member PHP API. The system will process two key events:
- `mandates.created` - Create member record with mandate reference and address details
- `payments.confirmed` - Create transaction records for successful payments

## Architecture

**Framework**: Custom PHP with Bramus Router v1.6, PDO/MySQL
**Authentication**: Bypass JWT for webhooks, use HMAC-SHA256 signature validation
**Idempotency**: Database-level unique constraints on webhook_id

## Critical Files

### New Files to Create

1. **`F:\source\repos\ka-member\api\webhooks\gocardless.php`**
   - Main webhook endpoint handler
   - Extracts raw payload and signature header
   - Validates signature and routes to controller

2. **`F:\source\repos\ka-member\api\models\webhook_log.php`**
   - Database model for webhook logging
   - Methods: `exists()`, `create()`, `markProcessed()`, `recordError()`
   - Provides idempotency checking

3. **`F:\source\repos\ka-member\api\models\gocardless_webhook.php`**
   - Core business logic for webhook processing
   - Signature validation (HMAC-SHA256)
   - Event parsing and routing
   - Methods: `validateSignature()`, `processEvents()`, `handleMandateCreated()`, `handlePaymentConfirmed()`

4. **`F:\source\repos\ka-member\api\controllers\webhook.controller.php`**
   - Controller to coordinate webhook processing
   - Error handling and response formatting
   - Orchestrates model interactions

### Files to Modify

5. **`F:\source\repos\ka-member\api\pre_routes.php`** (Lines 39, 74)
   - Add `$isWebhookPath = Headers::path_is_webhook($path);`
   - Change condition from `if (!$isAuthPath)` to `if (!$isAuthPath && !$isWebhookPath)`
   - Apply to both JWT validation middleware blocks

6. **`F:\source\repos\ka-member\api\core\headers.php`** (After line 43)
   - Add `path_is_webhook()` helper method
   - Pattern: `preg_match('/^webhook/', $path)`

7. **`F:\source\repos\ka-member\api\core\config.php`** (After line 67)
   - Add GoCardless webhook secret configuration
   - `Config::write('gocardless.webhook_secret', 'GOCARDLESS_WEBHOOK_SECRET');`

8. **`F:\source\repos\ka-member\api\routes.php`** (After line 215)
   - Mount webhook route group
   - Route: POST `/webhook/gocardless` → include `webhooks/gocardless.php`

9. **`F:\source\repos\ka-member\api\index.php`**
   - Include new models and controllers
   - Add after existing includes

## Database Schema

### New Table: webhook_log

```sql
CREATE TABLE `webhook_log` (
  `idwebhook_log` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `webhook_id` VARCHAR(255) NOT NULL,
  `event_id` VARCHAR(255) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `resource_id` VARCHAR(255) NULL,
  `payload` TEXT NOT NULL,
  `processed` TINYINT(1) DEFAULT 0,
  `idmember` INT UNSIGNED NULL,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL,
  UNIQUE KEY `unique_webhook_id` (`webhook_id`),
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_resource` (`resource_type`, `resource_id`),
  INDEX `idx_processed` (`processed`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Existing Tables (No Changes Required)

- `member.bankpayerref` - Already exists for storing mandate references
- `member` address fields - `addressfirstline`, `addresssecondline`, `city`, `county`, `postcode`
- `transaction` table - Has all fields needed (idtransaction, date, amount, note, member_idmember, bankID, paymenttypeID)

## Implementation Details

### Webhook Processing Flow

1. Receive POST to `/webhook/gocardless`
2. Extract raw payload (`file_get_contents('php://input')`)
3. Extract signature from `Webhook-Signature` header
4. Validate signature (HMAC-SHA256, timing-safe comparison)
5. Check idempotency (webhook_id in database)
6. Log webhook to `webhook_log` table
7. Parse JSON and process each event
8. Update member/transaction records
9. Mark webhook as processed
10. Return 200 OK (or appropriate error code)

### GoCardless Signature Validation

**Algorithm**: HMAC-SHA256
**Header**: `Webhook-Signature`
**Format**: `v1:signature_hash`

```php
$computed = hash_hmac('sha256', $payload, $webhook_secret);
$parts = explode(':', $signature);
return hash_equals($computed, $parts[1]); // timing-safe
```

### Event Handling

#### mandates.created Event

**Payload Structure**:
```json
{
  "events": [{
    "id": "EV123",
    "resource_type": "mandates",
    "action": "created",
    "links": {
      "mandate": "MD000123",
      "customer": "CU000456"
    },
    "details": {
      "scheme": "bacs"
    },
    "metadata": {
      "member_name": "John Smith",
      "business_name": "Smith Ltd",
      "email": "john@example.com",
      "mandate_type": "individual",
      "address_line1": "123 Main Street",
      "address_line2": "Flat 4",
      "city": "London",
      "county": "Greater London",
      "postcode": "SW1A 1AA"
    }
  }]
}
```

**Processing**:
1. Extract mandate ID from `links.mandate`
2. Extract member details from `metadata`:
   - Full name or business name
   - Email address
   - Mandate type (to determine membership status)
   - Address details (line1, line2, city, county, postcode)
3. Determine membership status based on mandate type
4. Create NEW member record with:
   - `bankpayerref` = mandate ID
   - `businessname` = name from metadata
   - `email1` = email from metadata
   - `addressfirstline` = address_line1
   - `addresssecondline` = address_line2
   - `city` = city
   - `county` = county
   - `postcode` = postcode
   - `joindate` = current date
   - `username` = 'gocardless_webhook'
   - `membership_idmembership` = status based on mandate type
5. Log success with new member ID

**Database Insert**:
```sql
INSERT INTO member
  (businessname, bankpayerref, email1, addressfirstline, addresssecondline,
   city, county, postcode, joindate, username, membership_idmembership)
VALUES
  (:businessname, :mandate_id, :email, :addressfirstline, :addresssecondline,
   :city, :county, :postcode, CURDATE(), 'gocardless_webhook', :membership_status_id)
```

**Mandate Type Mapping**:
Four mandate types are available:
1. **individual** - Individual member
2. **household** - Household member
3. **corporate** - Corporate/business member
4. **lifetime** - Lifetime member

Each maps to corresponding membership status in the database.

#### payments.confirmed Event

**Payload Structure**:
```json
{
  "events": [{
    "id": "EV124",
    "resource_type": "payments",
    "action": "confirmed",
    "links": {
      "payment": "PM000123",
      "mandate": "MD000123"
    },
    "details": {
      "amount": 5000,
      "currency": "GBP"
    }
  }]
}
```

**Processing**:
1. Extract payment ID, mandate ID, amount (in pence)
2. Find member by `bankpayerref` (mandate ID match)
3. Convert amount from pence to pounds (÷ 100)
4. Determine bankID and paymenttypeID (lookup or configure)
5. Create transaction record

**Database Insert**:
```sql
INSERT INTO transaction
  (date, amount, note, member_idmember, bankID, paymenttypeID)
VALUES
  (CURDATE(), :amount, :note, :idmember, :bankID, :paymenttypeID)
```

### Configuration Required

**Environment Variables** (add to Apache config or .htaccess):
```apache
SetEnv GOCARDLESS_WEBHOOK_SECRET "your_webhook_secret_here"
```

**Constants to Configure**:
- `gocardless.bank_id` - Database ID for GoCardless bank account
- `gocardless.payment_type_id` - Database ID for Direct Debit payment type

**Lookup queries** (run once to configure):
```sql
SELECT idbankAccount FROM bankaccount WHERE name = 'GoCardless' LIMIT 1;
SELECT idPaymentType FROM paymenttype WHERE name = 'Direct Debit' LIMIT 1;
```

## Error Handling & Response Codes

| Code | Scenario | Action |
|------|----------|--------|
| 200 | Success | Webhook processed successfully |
| 200 | Duplicate | Already processed (idempotency) |
| 401 | Invalid signature | Reject with error message |
| 400 | Invalid JSON | Reject with error message |
| 500 | Server error | Log error, return failure |

## Idempotency Strategy

**Mechanism**: Unique constraint on `webhook_log.webhook_id`

**Implementation**:
1. Check if `webhook_id` exists before processing
2. If exists, return 200 OK immediately
3. If not, create log entry (unique constraint prevents race conditions)
4. Process events and mark as completed

## Security Measures

1. **Signature Validation**: Always validate HMAC signature using timing-safe comparison
2. **Input Sanitization**: Use PDO prepared statements (already in place)
3. **Secret Storage**: Store webhook secret in environment variable, never in code
4. **Bypass JWT Only for Webhooks**: Limited scope, signature validation replaces JWT
5. **Error Messages**: Generic error responses, detailed logging internally

## Code Patterns to Follow

**Database Queries** (from existing codebase):
```php
$stmt = $this->conn->prepare($query);
$this->field = htmlspecialchars(strip_tags($this->field));
$stmt->bindParam(":field", $this->field);
if($stmt->execute()){ return true; }
```

**Model Constructor**:
```php
public function __construct(){
    $this->conn = \Core\Database::getInstance()->conn;
}
```

**Controller Response**:
```php
if($model->create()){
    echo json_encode(array("message" => "Success", "id" => $model->id));
} else {
    http_response_code(422);
    echo json_encode(array("message" => "Unable to create"));
}
```

## Testing Plan

### Local Testing Options

**Option 1: GoCardless Sandbox**
- Use test mode in GoCardless dashboard
- Configure webhook URL (may need ngrok for local dev)
- Send test webhooks from dashboard

**Option 2: Manual Simulation Script**
Create `test_webhook.php`:
```php
$payload = json_encode([...]);
$signature = 'v1:' . hash_hmac('sha256', $payload, $webhook_secret);

$ch = curl_init('http://localhost/api/webhook/gocardless');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Webhook-Signature: ' . $signature
]);
// ... send request
```

**Option 3: ngrok for Remote Testing**
```bash
ngrok http 80
# Use https://xyz.ngrok.io/api/webhook/gocardless
```

### Test Cases

1. ✓ Valid mandate.created webhook → member record created with address
2. ✓ Valid payment.confirmed webhook → transaction created
3. ✓ Invalid signature → 401 response
4. ✓ Duplicate webhook_id → 200 response, no duplicate processing
5. ✓ Unknown member mandate → 200 response, logged warning
6. ✓ Malformed JSON → 400 response
7. ✓ Missing webhook secret → 500 response
8. ✓ Multiple events in one webhook → all processed

## Verification Steps

After implementation:

1. **Database Setup**: Run SQL to create `webhook_log` table
2. **Configuration**: Set `GOCARDLESS_WEBHOOK_SECRET` environment variable
3. **Route Test**: `curl -X POST http://localhost/api/webhook/gocardless` (should accept request, fail on signature)
4. **Signature Test**: Send test webhook with valid signature
5. **Idempotency Test**: Send same webhook twice, verify only processed once
6. **Database Check**: Verify `webhook_log` entries created
7. **Member Update**: Check `member.bankpayerref` and address fields updated for mandate events
8. **Transaction Creation**: Check `transaction` records for payment events

## Implementation Sequence

1. Create database table (`webhook_log`)
2. Update configuration (`core/config.php`)
3. Add webhook path helper (`core/headers.php`)
4. Update authentication bypass (`pre_routes.php`)
5. Create models (`webhook_log.php`, `gocardless_webhook.php`)
6. Create controller (`webhook.controller.php`)
7. Create webhook endpoint (`webhooks/gocardless.php`)
8. Add route (`routes.php`)
9. Update includes (`index.php`)
10. Test with sample payloads

## Key Considerations

### New Member Creation from Mandate

**Approach**: When `mandates.created` event is received, create a new member record.

**Available Data from GoCardless**:
- Full name or business name
- Email address
- Mandate type (determines membership status)
- Address details (line1, line2, city, county, postcode)

**Implementation**:
1. Extract member details from webhook metadata
2. Determine membership status from mandate type
3. Create new member record with:
   - `businessname` = name from metadata
   - `bankpayerref` = mandate ID
   - `email1` = email from metadata
   - Address fields from metadata
   - `joindate` = current date
   - `username` = 'gocardless_webhook'
   - `membership_idmembership` = determined from mandate type
4. Store member ID in webhook log for reference

**Mandate Type to Membership Status Mapping**:
Configure mapping in `gocardless_webhook.php` for all four mandate types:
```php
private function getMembershipStatusFromMandateType($mandate_type) {
    // Query membershipstatus table to get IDs for each type
    $mapping = [
        'individual' => 1,   // Individual Member status ID
        'household' => 2,    // Household Member status ID
        'corporate' => 3,    // Corporate Member status ID
        'lifetime' => 4,     // Lifetime Member status ID
    ];

    if (!isset($mapping[$mandate_type])) {
        error_log("Unknown mandate type: $mandate_type");
        return null; // Will need to handle this error
    }

    return $mapping[$mandate_type];
}
```

**Setup Query** (run once to determine correct IDs):
```sql
SELECT idmembership, name FROM membershipstatus
WHERE name IN ('Individual', 'Household', 'Corporate', 'Lifetime');
```

Update the mapping array with actual database IDs.

### Address Field Handling

**GoCardless provides the following address fields**:
- `address_line1` - First line of address
- `address_line2` - Second line of address (optional)
- `city` - City/town
- `county` - County/state/region (optional)
- `postcode` - Postal/ZIP code

**Mapping to member table**:
- `address_line1` → `member.addressfirstline`
- `address_line2` → `member.addresssecondline`
- `city` → `member.city`
- `county` → `member.county`
- `postcode` → `member.postcode`

### Duplicate Payment Prevention

Check for existing transaction with same payment ID:
```sql
SELECT idtransaction FROM transaction
WHERE note LIKE '%PM000123%'
AND member_idmember = :member_id
LIMIT 1
```

### Future Enhancements

- Admin dashboard for webhook logs
- Additional event types (mandate cancelled, payment failed)
- Email notifications on processing failures
- Retry mechanism for failed processing
- Manual replay of failed webhooks

## Reference

**GoCardless Webhook Documentation**: https://developer.gocardless.com/getting-started/api/webhooks/

**Key Event Types**:
- `mandates.created`, `mandates.active`, `mandates.cancelled`
- `payments.created`, `payments.confirmed`, `payments.paid_out`, `payments.failed`

---

**Estimated Effort**: 4-6 hours for initial implementation + 2-3 hours for testing

---

## Implementation Status

✅ **COMPLETED**

All implementation has been finished. Files created and modified as per the plan. Ready for setup, configuration, and testing.

### Deliverables

1. ✅ Database schema (webhook_log_table.sql)
2. ✅ Webhook log model (webhook_log.php)
3. ✅ GoCardless webhook model with address support (gocardless_webhook.php)
4. ✅ Webhook controller (webhook.controller.php)
5. ✅ Webhook endpoint (webhooks/gocardless.php)
6. ✅ Configuration updates (config.php)
7. ✅ Header helper (headers.php)
8. ✅ Authentication bypass (pre_routes.php)
9. ✅ Route registration (routes.php)
10. ✅ Index includes (index.php)
11. ✅ Setup and testing guide (SETUP_AND_TESTING.md)
12. ✅ Implementation summary (IMPLEMENTATION_SUMMARY.md)

**Next Steps**: Follow SETUP_AND_TESTING.md for deployment instructions.
