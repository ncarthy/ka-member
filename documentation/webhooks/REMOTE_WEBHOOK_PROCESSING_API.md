# Remote Webhook Processing API

## Overview

The webhook queue processor can now be called remotely from another computer using authenticated API endpoints. This allows you to trigger webhook processing, check queue statistics, and reset stuck events without direct server access.

## Authentication

All remote webhook processing endpoints require authentication using the same JWT token mechanism used by other API endpoints.

### Getting an Access Token

1. Authenticate with the API:

```bash
POST https://your-api-domain/auth
Content-Type: application/json

{
  "username": "your_username",
  "password": "your_password"
}
```

2. Response contains an access token:

```json
{
  "username": "your_username",
  "id": 1,
  "role": "Admin",
  "fullname": "Your Name",
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

3. Use the `accessToken` in all subsequent requests in the Authorization header:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

### Authorization Requirements

- **All webhook processor endpoints require authentication** (valid JWT token)
- **Admin role required** for POST/PUT/DELETE operations (enforced by existing middleware)
- The webhook receiver endpoint (`/webhook/gocardless`) remains unauthenticated for GoCardless callbacks

## API Endpoints

### 1. Process Webhook Queue

**Endpoint:** `POST /queue/process`

Process pending webhook events from the queue.

**Request Body:**
```json
{
  "batch_size": 20,
  "iterations": 5,
  "sleep_seconds": 2
}
```

**Parameters:**
- `batch_size` (optional): Number of events to process per batch (default: 10, max: 100)
- `iterations` (optional): Number of processing iterations (default: 1, max: 50)
- `sleep_seconds` (optional): Seconds to sleep between iterations (default: 0, max: 60)

**Response:**
```json
{
  "message": "Processing completed",
  "batch_size": 20,
  "iterations": 5,
  "processed_events": 23,
  "failed_events": 2,
  "output": "Detailed processing output..."
}
```

**cURL Example:**
```bash
curl -X POST https://your-api-domain/queue/process \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "batch_size": 10,
    "iterations": 1
  }'
```

**Python Example:**
```python
import requests

url = "https://your-api-domain/queue/process"
headers = {
    "Authorization": "Bearer YOUR_ACCESS_TOKEN",
    "Content-Type": "application/json"
}
data = {
    "batch_size": 10,
    "iterations": 1
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

---

### 2. Get Queue Statistics

**Endpoint:** `GET /queue/stats`

Retrieve current queue statistics.

**Response:**
```json
{
  "message": "Queue statistics",
  "stats": {
    "pending": 15,
    "processing": 2,
    "completed": 1247,
    "failed": 3
  }
}
```

**cURL Example:**
```bash
curl -X GET https://your-api-domain/queue/stats \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

**Python Example:**
```python
import requests

url = "https://your-api-domain/queue/stats"
headers = {
    "Authorization": "Bearer YOUR_ACCESS_TOKEN"
}

response = requests.get(url, headers=headers)
print(response.json())
```

---

### 3. Reset Stuck Events

**Endpoint:** `POST /queue/reset-stuck`

Reset events that have been stuck in "processing" state for too long.

**Request Body:**
```json
{
  "timeout_minutes": 30
}
```

**Parameters:**
- `timeout_minutes` (optional): Minutes before considering an event stuck (default: 30, max: 1440)

**Response:**
```json
{
  "message": "Stuck events reset",
  "reset_count": 2,
  "timeout_minutes": 30
}
```

**cURL Example:**
```bash
curl -X POST https://your-api-domain/queue/reset-stuck \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "timeout_minutes": 30
  }'
```

**Python Example:**
```python
import requests

url = "https://your-api-domain/queue/reset-stuck"
headers = {
    "Authorization": "Bearer YOUR_ACCESS_TOKEN",
    "Content-Type": "application/json"
}
data = {
    "timeout_minutes": 30
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

---

## Complete Python Script Example

Here's a complete Python script that demonstrates authenticating and processing webhooks:

```python
import requests
import json
import time

class WebhookProcessor:
    def __init__(self, api_url, username, password):
        self.api_url = api_url.rstrip('/')
        self.username = username
        self.password = password
        self.access_token = None

    def authenticate(self):
        """Authenticate and get access token"""
        url = f"{self.api_url}/auth"
        data = {
            "username": self.username,
            "password": self.password
        }

        response = requests.post(url, json=data)
        response.raise_for_status()

        result = response.json()
        self.access_token = result['accessToken']
        print(f"Authenticated as {result['username']} ({result['role']})")
        return self.access_token

    def get_headers(self):
        """Get headers with authentication"""
        if not self.access_token:
            raise Exception("Not authenticated. Call authenticate() first.")

        return {
            "Authorization": f"Bearer {self.access_token}",
            "Content-Type": "application/json"
        }

    def get_stats(self):
        """Get queue statistics"""
        url = f"{self.api_url}/queue/stats"
        response = requests.get(url, headers=self.get_headers())
        response.raise_for_status()
        return response.json()

    def process(self, batch_size=10, iterations=1, sleep_seconds=0):
        """Process webhook queue"""
        url = f"{self.api_url}/queue/process"
        data = {
            "batch_size": batch_size,
            "iterations": iterations,
            "sleep_seconds": sleep_seconds
        }

        response = requests.post(url, headers=self.get_headers(), json=data)
        response.raise_for_status()
        return response.json()

    def reset_stuck(self, timeout_minutes=30):
        """Reset stuck events"""
        url = f"{self.api_url}/queue/reset-stuck"
        data = {
            "timeout_minutes": timeout_minutes
        }

        response = requests.post(url, headers=self.get_headers(), json=data)
        response.raise_for_status()
        return response.json()

# Usage example
if __name__ == "__main__":
    # Initialize processor
    processor = WebhookProcessor(
        api_url="https://your-api-domain",
        username="your_username",
        password="your_password"
    )

    # Authenticate
    processor.authenticate()

    # Get current stats
    stats = processor.get_stats()
    print(f"\nQueue stats: {json.dumps(stats, indent=2)}")

    # Process pending events
    if stats['stats']['pending'] > 0:
        print(f"\nProcessing {stats['stats']['pending']} pending events...")
        result = processor.process(batch_size=10, iterations=1)
        print(f"Processed: {result['processed_events']}, Failed: {result['failed_events']}")

    # Reset stuck events if needed
    if stats['stats']['processing'] > 5:
        print("\nResetting stuck events...")
        reset_result = processor.reset_stuck(timeout_minutes=30)
        print(f"Reset {reset_result['reset_count']} stuck events")
```

---

## Error Responses

### Authentication Error (401)
```json
{
  "message": "Not logged in."
}
```

### Authorization Error (401)
```json
{
  "message": "Must be an admin."
}
```

### Validation Error (400)
```json
{
  "error": "batch_size must be between 1 and 100"
}
```

### Server Error (500)
```json
{
  "error": "Processing failed",
  "message": "Detailed error message"
}
```

---

## Comparison: CLI vs Remote API

### CLI Script (Local Server Only)

```bash
# Process one batch
php cli/process_webhooks.php --once

# Show statistics
php cli/process_webhooks.php --stats

# Reset stuck events
php cli/process_webhooks.php --reset-stuck
```

**Use CLI when:**
- Running on the server directly
- Setting up cron jobs or supervisord
- Continuous background processing needed

### Remote API (From Any Computer)

```bash
# Process one batch
curl -X POST https://api/queue/process \
  -H "Authorization: Bearer TOKEN" \
  -d '{"batch_size":10,"iterations":1}'

# Show statistics
curl -X GET https://api/queue/stats \
  -H "Authorization: Bearer TOKEN"

# Reset stuck events
curl -X POST https://api/queue/reset-stuck \
  -H "Authorization: Bearer TOKEN" \
  -d '{"timeout_minutes":30}'
```

**Use Remote API when:**
- Calling from another computer/server
- Integrating with monitoring systems
- Building dashboards or management tools
- Triggering processing from external systems

---

## Security Considerations

1. **Use HTTPS:** Always use HTTPS in production to protect authentication tokens
2. **Token Expiry:** Access tokens expire after a configured time (check your config)
3. **Admin Role:** Webhook processing requires admin privileges
4. **Rate Limiting:** Consider implementing rate limiting for these endpoints
5. **Network Security:** Restrict access to these endpoints via firewall rules if needed

---

## Implementation Files

### Created Files
- `api/controllers/webhook_processor.controller.php` - Controller for remote webhook processing

### Modified Files
- `api/index.php` - Added webhook_processor controller include
- `api/routes.php` - Added webhook processor routes

### Unchanged Files
- `api/cli/process_webhooks.php` - CLI script still works as before
- `api/workers/webhook_queue_processor.php` - Worker class used by both CLI and API

---

## Testing

### 1. Test Authentication
```bash
curl -X POST https://your-api-domain/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}'
```

### 2. Test Stats Endpoint
```bash
curl -X GET https://your-api-domain/queue/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Processing
```bash
curl -X POST https://your-api-domain/queue/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"batch_size":5,"iterations":1}'
```

---

## Monitoring and Automation

### Example: Scheduled Remote Processing

Use this Python script with cron or Task Scheduler:

```python
#!/usr/bin/env python3
# webhook_monitor.py

import requests
import sys
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

API_URL = "https://your-api-domain"
USERNAME = "admin"
PASSWORD = "your_password"

def main():
    try:
        # Authenticate
        auth_response = requests.post(
            f"{API_URL}/auth",
            json={"username": USERNAME, "password": PASSWORD}
        )
        auth_response.raise_for_status()
        token = auth_response.json()['accessToken']

        headers = {"Authorization": f"Bearer {token}"}

        # Check stats
        stats_response = requests.get(f"{API_URL}/queue/stats", headers=headers)
        stats_response.raise_for_status()
        stats = stats_response.json()['stats']

        logger.info(f"Queue stats: {stats}")

        # Process if pending events exist
        if stats['pending'] > 0:
            logger.info(f"Processing {stats['pending']} pending events")
            process_response = requests.post(
                f"{API_URL}/queue/process",
                headers=headers,
                json={"batch_size": 10, "iterations": 1}
            )
            process_response.raise_for_status()
            result = process_response.json()
            logger.info(f"Processed: {result['processed_events']}, Failed: {result['failed_events']}")
        else:
            logger.info("No pending events")

    except Exception as e:
        logger.error(f"Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
```

Add to crontab (run every 5 minutes):
```
*/5 * * * * /usr/bin/python3 /path/to/webhook_monitor.py >> /var/log/webhook_monitor.log 2>&1
```

---

## Summary

The webhook queue processor now supports:
- ✅ Remote API access with JWT authentication
- ✅ Same functionality as CLI script
- ✅ Backward compatible (CLI still works)
- ✅ Secure (requires admin authentication)
- ✅ Easy to integrate with external systems
- ✅ Suitable for monitoring and automation

The CLI script remains the best choice for continuous background processing (via supervisord or systemd), while the API endpoints are perfect for:
- Remote monitoring dashboards
- Integration with other systems
- On-demand processing triggers
- Scheduled remote processing from another server
