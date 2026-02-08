# Remote Webhook Processing API - Implementation Summary

## Overview

The background CLI webhook processor (`api/cli/process_webhooks.php`) can now be called remotely from another computer using authenticated API endpoints. The existing CLI script remains unchanged and fully functional.

## What Was Implemented

### 1. New Controller: `webhook_processor.controller.php`

**Location:** `F:\source\repos\ka-member\api\controllers\webhook_processor.controller.php`

**Purpose:** Provides authenticated API endpoints for webhook queue operations

**Methods:**
- `process()` - Process webhook queue (POST /queue/process)
- `stats()` - Get queue statistics (GET /queue/stats)
- `resetStuck()` - Reset stuck events (POST /queue/reset-stuck)

**Key Features:**
- JSON request/response format
- Input validation
- Error handling
- Output capture from worker
- Statistics reporting

### 2. Updated Routes

**File:** `api/routes.php`

**Added Routes:**
```php
$router->post('/queue/process', 'WebhookProcessorCtl@process');
$router->get('/queue/stats', 'WebhookProcessorCtl@stats');
$router->post('/queue/reset-stuck', 'WebhookProcessorCtl@resetStuck');
```

**Authentication:** All routes are protected by existing JWT authentication middleware in `pre_routes.php`

### 3. Updated Includes

**File:** `api/index.php`

**Added:**
```php
include_once 'controllers/webhook_processor.controller.php';
```

## How It Works

### Authentication Flow

```
1. Client authenticates → POST /auth with credentials
2. Server returns JWT access token
3. Client includes token in Authorization header
4. Middleware validates token (pre_routes.php)
5. Controller processes request
6. Response returned as JSON
```

### API Endpoints

#### 1. Process Webhooks
```
POST /queue/process
Authorization: Bearer <token>
Content-Type: application/json

{
  "batch_size": 10,
  "iterations": 1,
  "sleep_seconds": 0
}
```

#### 2. Get Statistics
```
GET /queue/stats
Authorization: Bearer <token>
```

#### 3. Reset Stuck Events
```
POST /queue/reset-stuck
Authorization: Bearer <token>
Content-Type: application/json

{
  "timeout_minutes": 30
}
```

## Authentication Mechanism

Uses the **existing API authentication system**:

1. **JWT Tokens:** Same token system used by all API endpoints
2. **Authorization Header:** `Bearer <token>` format
3. **Middleware:** Automatic validation via `pre_routes.php`
4. **Admin Required:** POST operations require admin role
5. **Token Expiry:** Configurable expiration time

**No changes were made to the authentication system** - it leverages what's already in place.

## Files Changed

### Created Files
1. `api/controllers/webhook_processor.controller.php` (149 lines)
2. `F:\claude\gocardless\REMOTE_WEBHOOK_PROCESSING_API.md` (documentation)

### Modified Files
1. `api/index.php` - Added 1 line (include controller)
2. `api/routes.php` - Added 3 route definitions

### Unchanged Files
- `api/cli/process_webhooks.php` - CLI script still works exactly as before
- `api/workers/webhook_queue_processor.php` - Worker class unchanged
- `api/models/webhook_queue.php` - Model unchanged
- `api/authenticate/*` - Authentication system unchanged
- `api/pre_routes.php` - Middleware unchanged

## Usage Examples

### From Command Line (cURL)

```bash
# 1. Get access token
TOKEN=$(curl -X POST https://your-api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"pass"}' \
  | jq -r '.accessToken')

# 2. Check queue stats
curl -X GET https://your-api/queue/stats \
  -H "Authorization: Bearer $TOKEN"

# 3. Process webhooks
curl -X POST https://your-api/queue/process \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"batch_size":10,"iterations":1}'
```

### From Python

```python
import requests

# Authenticate
response = requests.post('https://your-api/auth', json={
    'username': 'admin',
    'password': 'pass'
})
token = response.json()['accessToken']

# Use the API
headers = {'Authorization': f'Bearer {token}'}

# Get stats
stats = requests.get('https://your-api/queue/stats', headers=headers)
print(stats.json())

# Process webhooks
result = requests.post('https://your-api/queue/process',
    headers=headers,
    json={'batch_size': 10, 'iterations': 1}
)
print(result.json())
```

### From PowerShell (Windows)

```powershell
# Authenticate
$body = @{
    username = "admin"
    password = "pass"
} | ConvertTo-Json

$authResponse = Invoke-RestMethod -Uri "https://your-api/auth" `
    -Method Post -Body $body -ContentType "application/json"

$token = $authResponse.accessToken

# Get stats
$headers = @{
    Authorization = "Bearer $token"
}

$stats = Invoke-RestMethod -Uri "https://your-api/queue/stats" `
    -Method Get -Headers $headers

Write-Output $stats

# Process webhooks
$processBody = @{
    batch_size = 10
    iterations = 1
} | ConvertTo-Json

$result = Invoke-RestMethod -Uri "https://your-api/queue/process" `
    -Method Post -Headers $headers -Body $processBody `
    -ContentType "application/json"

Write-Output $result
```

## Security

### Built-in Security (Already Present)
- ✅ JWT token authentication
- ✅ Token expiration
- ✅ Admin role requirement for modifications
- ✅ CORS headers configured
- ✅ Signature validation for incoming webhooks

### Additional Recommendations
- ✅ Use HTTPS in production (recommended for any API)
- ✅ Configure firewall rules if needed
- ✅ Implement rate limiting if desired (not included)
- ✅ Regular token rotation (already supported)

## Use Cases

### 1. Remote Monitoring Dashboard
Build a web dashboard on another server that monitors and triggers webhook processing.

### 2. Scheduled Remote Processing
Run a script from a different server (e.g., your local machine) to trigger processing:
```bash
# On your local machine, schedule this:
*/5 * * * * /path/to/trigger_remote_processing.sh
```

### 3. Integration with Other Systems
Integrate with monitoring tools (Nagios, Zabbix, etc.) to check queue health:
```python
# Check if queue is backing up
stats = get_webhook_stats()
if stats['pending'] > 100:
    alert("Webhook queue backing up!")
    trigger_processing()
```

### 4. Manual On-Demand Processing
Trigger webhook processing from your workstation without SSH access:
```bash
# From your laptop
curl -X POST https://api/queue/process \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"batch_size":50,"iterations":1}'
```

## Comparison: CLI vs API

| Feature | CLI Script | Remote API |
|---------|-----------|------------|
| **Access** | Server only | Any computer |
| **Authentication** | None (local) | JWT required |
| **Use Case** | Background processing | Remote management |
| **Continuous Mode** | ✅ Yes | ❌ No (use iterations) |
| **Supervisord/Systemd** | ✅ Recommended | ❌ Not suitable |
| **Monitoring Integration** | ❌ Difficult | ✅ Easy |
| **Remote Dashboards** | ❌ No | ✅ Yes |
| **Cron Jobs** | ✅ Yes (local) | ✅ Yes (remote) |

## Testing Checklist

- [ ] Test authentication endpoint
- [ ] Test stats endpoint with valid token
- [ ] Test stats endpoint without token (should fail)
- [ ] Test processing endpoint with valid admin token
- [ ] Test processing endpoint with non-admin token (should fail)
- [ ] Test reset-stuck endpoint
- [ ] Verify CLI script still works unchanged
- [ ] Test invalid parameters (should return 400 errors)
- [ ] Test with expired token (should return 401 error)
- [ ] Verify output matches expected format

## Deployment Notes

### No Changes Required For:
- Existing cron jobs running CLI script
- Existing supervisord/systemd configurations
- Webhook receiver endpoint (`/webhook/gocardless`)
- Authentication system or user credentials

### New Capabilities:
- Can now trigger processing from monitoring systems
- Can build remote dashboards
- Can integrate with other services
- Can manually trigger from workstation

## Backward Compatibility

✅ **100% Backward Compatible**

- CLI script unchanged
- Existing cron jobs unaffected
- Authentication system unchanged
- Database schema unchanged
- Webhook handlers unchanged
- All existing functionality preserved

## Performance Considerations

### Remote API Calls
- Each API call spawns the processor for the specified iterations
- Suitable for on-demand or periodic processing (not continuous)
- For continuous processing, use the CLI script with supervisord

### Recommended Usage Patterns

**Good:**
```python
# Check stats, process if needed
stats = get_stats()
if stats['pending'] > 10:
    process(batch_size=20, iterations=1)
```

**Not Recommended:**
```python
# Don't do this - use CLI script instead
while True:
    process(batch_size=10, iterations=1)
    time.sleep(10)
```

## Troubleshooting

### Issue: "Not logged in" error
**Solution:** Include valid JWT token in Authorization header

### Issue: "Must be an admin" error
**Solution:** Authenticate with admin account

### Issue: Token expired
**Solution:** Re-authenticate to get new token

### Issue: Connection refused
**Solution:** Verify API URL and network connectivity

### Issue: No events processing
**Solution:** Check queue stats first - may be no pending events

## Next Steps

1. **Test the API:**
   ```bash
   # Get token and test endpoints
   ```

2. **Build Integration:**
   - Create monitoring script
   - Add to your dashboard
   - Set up scheduled remote processing

3. **Documentation:**
   - Share API endpoints with team
   - Update internal documentation
   - Add to monitoring procedures

## Documentation Files

- **API Documentation:** `REMOTE_WEBHOOK_PROCESSING_API.md`
- **Implementation Summary:** `IMPLEMENTATION_SUMMARY_REMOTE_API.md` (this file)
- **Original Queue Documentation:** `QUEUE_IMPLEMENTATION_SUMMARY.md`

## Summary

Successfully implemented remote API access to the webhook processor with:
- ✅ Three authenticated API endpoints
- ✅ Full backward compatibility
- ✅ Uses existing authentication system
- ✅ Comprehensive documentation
- ✅ Example code in multiple languages
- ✅ Security through existing JWT tokens
- ✅ Zero disruption to existing functionality

**Total Changes:** 1 new file (controller), 2 small edits (index.php, routes.php)

**Result:** CLI script can now be called remotely from any computer with proper authentication.
