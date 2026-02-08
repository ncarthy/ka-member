# GoCardless Webhook Integration - Setup and Testing Guide

## Implementation Summary

The GoCardless webhook integration has been successfully implemented with the following components:

### Files Created

1. **`webhook_log_table.sql`** - Database schema for webhook logging
2. **`api/models/webhook_log.php`** - Model for webhook log database operations
3. **`api/models/gocardless_webhook.php`** - Core business logic for webhook processing
4. **`api/controllers/webhook.controller.php`** - Controller for webhook orchestration
5. **`api/webhooks/gocardless.php`** - Main webhook endpoint

### Files Modified

1. **`api/core/config.php`** - Added GoCardless webhook secret configuration
2. **`api/core/headers.php`** - Added `path_is_webhook()` helper method
3. **`api/pre_routes.php`** - Updated to bypass JWT authentication for webhook routes
4. **`api/routes.php`** - Added `/webhook/gocardless` route
5. **`api/index.php`** - Added includes for new models and controller

---

## Setup Instructions

### 1. Database Setup

Run the SQL script to create the `webhook_log` table:

```bash
mysql -u your_user -p knightsb_membership < webhook_log_table.sql
```

Or execute the SQL directly in your database management tool.

### 2. Configure GoCardless Webhook Secret

Set the webhook secret as an environment variable. The secret is provided by GoCardless when you create a webhook endpoint in their dashboard.

**Development (Apache httpd.conf):**
```apache
SetEnv GOCARDLESS_WEBHOOK_SECRET "your_webhook_secret_from_gocardless"
```

**Production (.htaccess in api directory):**
```apache
SetEnv GOCARDLESS_WEBHOOK_SECRET "your_webhook_secret_from_gocardless"
```

### 3. Configure Payment Constants

Update the constants in `api/models/gocardless_webhook.php` (lines 11-12) with correct database IDs:

**Find the correct IDs:**
```sql
-- Get GoCardless bank account ID
SELECT idbankAccount FROM bankaccount WHERE name = 'GoCardless' LIMIT 1;

-- Get Direct Debit payment type ID
SELECT idPaymentType FROM paymenttype WHERE name = 'Direct Debit' LIMIT 1;
```

**Update the constants:**
```php
const BANK_ID = 1; // Replace with correct bankaccount.idbankAccount
const PAYMENT_TYPE_ID = 1; // Replace with correct paymenttype.idPaymentType
```

### 4. Configure Membership Status Mapping

Update the membership status mapping in `api/models/gocardless_webhook.php` (method `getMembershipStatusFromMandateType`, starting around line 250):

**Find the correct IDs:**
```sql
SELECT idmembership, name FROM membershipstatus
WHERE name IN ('Individual', 'Household', 'Corporate', 'Lifetime');
```

**Update the mapping array:**
```php
private function getMembershipStatusFromMandateType($mandate_type) {
    $mapping = [
        'individual' => 1,   // Replace with actual Individual status ID
        'household' => 2,    // Replace with actual Household status ID
        'corporate' => 3,    // Replace with actual Corporate status ID
        'lifetime' => 4,     // Replace with actual Lifetime status ID
    ];
    // ...
}
```

### 5. Register Webhook Endpoint in GoCardless

1. Log in to GoCardless Dashboard
2. Navigate to Developers → Webhooks
3. Add a new webhook endpoint:
   - **URL**: `https://your-domain.com/api/webhook/gocardless`
   - **Events**: Select `mandates.created` and `payments.confirmed`
4. Copy the webhook secret and use it in step 2 above

---

## Testing

### Test 1: Basic Connectivity

Test that the endpoint accepts POST requests:

```bash
curl -X POST http://localhost/api/webhook/gocardless \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

Expected: `{"message":"Missing signature"}` (400 error - this is correct, signature validation is working)

### Test 2: Signature Validation

Create a test script `test_webhook_signature.php`:

```php
<?php
$webhook_secret = 'your_test_secret'; // Match your configured secret

$payload = json_encode([
    'events' => [
        [
            'id' => 'TEST001',
            'resource_type' => 'mandates',
            'action' => 'created',
            'links' => ['mandate' => 'MD000TEST'],
            'metadata' => [
                'member_name' => 'Test User',
                'email' => 'test@example.com',
                'mandate_type' => 'individual',
                'address_line1' => '123 Test Street',
                'city' => 'London',
                'postcode' => 'SW1A 1AA'
            ]
        ]
    ]
]);

$signature = 'v1:' . hash_hmac('sha256', $payload, $webhook_secret);

$ch = curl_init('http://localhost/api/webhook/gocardless');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Webhook-Signature: ' . $signature
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $response\n";
```

Run: `php test_webhook_signature.php`

Expected: HTTP 200 with success message

### Test 3: Mandate Created Event

Send a test `mandates.created` webhook:

```json
{
  "events": [{
    "id": "EV_TEST_MANDATE_001",
    "resource_type": "mandates",
    "action": "created",
    "links": {
      "mandate": "MD000TEST123",
      "customer": "CU000TEST456"
    },
    "details": {
      "scheme": "bacs"
    },
    "metadata": {
      "member_name": "John Smith",
      "email": "john.smith@example.com",
      "mandate_type": "individual",
      "address_line1": "123 Test Street",
      "address_line2": "Flat 4",
      "city": "London",
      "county": "Greater London",
      "postcode": "SW1A 1AA"
    }
  }]
}
```

**Verify:**
```sql
-- Check webhook was logged
SELECT * FROM webhook_log WHERE event_id = 'EV_TEST_MANDATE_001';

-- Check member was created
SELECT idmember, businessname, bankpayerref, email1, addressfirstline, city, postcode
FROM member
WHERE bankpayerref = 'MD000TEST123';
```

### Test 4: Payment Confirmed Event

Send a test `payments.confirmed` webhook (use the mandate ID from Test 3):

```json
{
  "events": [{
    "id": "EV_TEST_PAYMENT_001",
    "resource_type": "payments",
    "action": "confirmed",
    "links": {
      "payment": "PM000TEST789",
      "mandate": "MD000TEST123"
    },
    "details": {
      "amount": 5000,
      "currency": "GBP"
    }
  }]
}
```

**Verify:**
```sql
-- Check webhook was logged
SELECT * FROM webhook_log WHERE event_id = 'EV_TEST_PAYMENT_001';

-- Check transaction was created
SELECT t.idtransaction, t.date, t.amount, t.note, t.member_idmember, m.businessname
FROM transaction t
JOIN member m ON t.member_idmember = m.idmember
WHERE t.note LIKE '%PM000TEST789%';
```

Expected: Transaction with amount = 50.00 (converted from 5000 pence)

### Test 5: Idempotency

Resend the same webhook from Test 3 or Test 4.

Expected: HTTP 200 response with `"status": "duplicate"`, no new database records created.

### Test 6: Invalid Signature

Send a webhook with an incorrect signature:

```bash
curl -X POST http://localhost/api/webhook/gocardless \
  -H "Content-Type: application/json" \
  -H "Webhook-Signature: v1:invalid_signature_here" \
  -d '{"events": []}'
```

Expected: HTTP 401 with `{"message":"Invalid signature"}`

### Test 7: Using ngrok for GoCardless Testing

If you want to test with actual GoCardless sandbox webhooks:

1. Install ngrok: https://ngrok.com/
2. Start ngrok: `ngrok http 80`
3. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
4. In GoCardless dashboard, set webhook URL to: `https://abc123.ngrok.io/api/webhook/gocardless`
5. Send test webhooks from GoCardless dashboard

---

## Monitoring and Troubleshooting

### Check Webhook Logs

```sql
-- View all webhooks
SELECT * FROM webhook_log ORDER BY created_at DESC;

-- View unprocessed webhooks
SELECT * FROM webhook_log WHERE processed = 0;

-- View webhooks with errors
SELECT * FROM webhook_log WHERE error_message IS NOT NULL;

-- View webhooks by event type
SELECT resource_type, action, COUNT(*) as count
FROM webhook_log
GROUP BY resource_type, action;
```

### Check PHP Error Logs

The implementation logs errors using `error_log()`. Check your PHP error log location:

```bash
# Find error log location
php -i | grep error_log

# Tail the log
tail -f /path/to/php_error.log
```

### Common Issues

**Issue: "Invalid signature" errors**
- Verify `GOCARDLESS_WEBHOOK_SECRET` environment variable is set correctly
- Ensure the secret matches what's in GoCardless dashboard
- Check Apache has loaded the environment variable (restart Apache after changes)

**Issue: "Unknown mandate type" errors**
- Check the `mandate_type` value in metadata
- Verify the mapping in `getMembershipStatusFromMandateType()` includes all expected types
- Add logging to see what mandate_type values are being received

**Issue: "No member found with mandate ID" for payment webhooks**
- Ensure the `mandates.created` webhook was processed first
- Check the member record exists with correct `bankpayerref`
- Verify mandate ID matches between events

**Issue: "Failed to create member record"**
- Check database permissions
- Verify all required fields are provided
- Check for duplicate `bankpayerref` if you have unique constraints

---

## Address Fields

GoCardless provides address information when users sign up for Direct Debit mandates. The implementation captures the following address fields from the webhook metadata:

- `address_line1` → stored in `member.addressfirstline`
- `address_line2` → stored in `member.addresssecondline`
- `city` → stored in `member.city`
- `county` → stored in `member.county`
- `postcode` → stored in `member.postcode`

Make sure your GoCardless integration passes these fields in the webhook metadata when creating mandates.

---

## Security Considerations

1. **Signature Validation**: All webhooks are validated using HMAC-SHA256 before processing
2. **Timing-Safe Comparison**: Uses `hash_equals()` to prevent timing attacks
3. **Input Sanitization**: All inputs are sanitized using `htmlspecialchars()` and `strip_tags()`
4. **Prepared Statements**: All database queries use PDO prepared statements
5. **Environment Variables**: Webhook secret stored in environment variable, never in code
6. **Idempotency**: Unique constraint on `webhook_id` prevents duplicate processing
7. **Error Handling**: Generic error messages returned to GoCardless, detailed logging internally

---

## Production Deployment Checklist

- [ ] Run `webhook_log_table.sql` on production database
- [ ] Set `GOCARDLESS_WEBHOOK_SECRET` in production environment
- [ ] Update `BANK_ID` constant with correct production value
- [ ] Update `PAYMENT_TYPE_ID` constant with correct production value
- [ ] Update membership status mapping with correct production IDs
- [ ] Register webhook endpoint in GoCardless production account
- [ ] Test with GoCardless sandbox first
- [ ] Monitor webhook logs after deployment
- [ ] Set up alerts for webhook processing errors
- [ ] Document webhook secret location for team

---

## Future Enhancements

1. **Admin Dashboard**: Create UI to view webhook logs and retry failed webhooks
2. **Additional Events**: Handle `mandate.cancelled`, `payment.failed`, etc.
3. **Email Notifications**: Alert admins when webhook processing fails
4. **Retry Mechanism**: Automatic retry for transient errors
5. **Manual Replay**: Allow admins to manually replay failed webhooks
6. **Webhook Analytics**: Track processing times, success rates, etc.

---

## Support

For issues with:
- **GoCardless API**: https://developer.gocardless.com/
- **Webhook Integration**: Check this repository's issues
- **Database Errors**: Review MySQL error logs and webhook_log table
