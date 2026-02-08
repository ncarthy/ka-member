# Quick Reference: Remote Webhook Processing API

## API Endpoints

### Base URL
```
https://your-api-domain
```

---

## 1. Authenticate

```bash
POST /auth
```

**Request:**
```json
{
  "username": "admin",
  "password": "your_password"
}
```

**Response:**
```json
{
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "username": "admin",
  "role": "Admin"
}
```

---

## 2. Get Queue Stats

```bash
GET /queue/stats
Authorization: Bearer <token>
```

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

---

## 3. Process Webhooks

```bash
POST /queue/process
Authorization: Bearer <token>
Content-Type: application/json
```

**Request:**
```json
{
  "batch_size": 10,
  "iterations": 1,
  "sleep_seconds": 0
}
```

**Response:**
```json
{
  "message": "Processing completed",
  "processed_events": 8,
  "failed_events": 0
}
```

---

## 4. Reset Stuck Events

```bash
POST /queue/reset-stuck
Authorization: Bearer <token>
Content-Type: application/json
```

**Request:**
```json
{
  "timeout_minutes": 30
}
```

**Response:**
```json
{
  "message": "Stuck events reset",
  "reset_count": 2
}
```

---

## cURL Examples

### Get Token
```bash
TOKEN=$(curl -s -X POST https://your-api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"pass"}' \
  | jq -r '.accessToken')
```

### Check Stats
```bash
curl -X GET https://your-api/queue/stats \
  -H "Authorization: Bearer $TOKEN"
```

### Process Once
```bash
curl -X POST https://your-api/queue/process \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"batch_size":10,"iterations":1}'
```

### Reset Stuck
```bash
curl -X POST https://your-api/queue/reset-stuck \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"timeout_minutes":30}'
```

---

## Python One-Liner

```python
import requests

# Authenticate and get token
token = requests.post('https://your-api/auth',
    json={'username':'admin','password':'pass'}).json()['accessToken']

# Use token
headers = {'Authorization': f'Bearer {token}'}

# Get stats
stats = requests.get('https://your-api/queue/stats', headers=headers).json()

# Process
result = requests.post('https://your-api/queue/process',
    headers=headers, json={'batch_size':10,'iterations':1}).json()
```

---

## PowerShell One-Liner

```powershell
# Authenticate
$token = (Invoke-RestMethod -Uri "https://your-api/auth" -Method Post `
  -Body (@{username="admin";password="pass"}|ConvertTo-Json) `
  -ContentType "application/json").accessToken

# Use token
$headers = @{Authorization = "Bearer $token"}

# Get stats
Invoke-RestMethod -Uri "https://your-api/queue/stats" -Headers $headers

# Process
Invoke-RestMethod -Uri "https://your-api/queue/process" -Method Post `
  -Headers $headers -Body (@{batch_size=10;iterations=1}|ConvertTo-Json) `
  -ContentType "application/json"
```

---

## Parameters

### Process Endpoint

| Parameter | Type | Default | Min | Max | Description |
|-----------|------|---------|-----|-----|-------------|
| batch_size | int | 10 | 1 | 100 | Events per batch |
| iterations | int | 1 | 1 | 50 | Processing iterations |
| sleep_seconds | int | 0 | 0 | 60 | Sleep between iterations |

### Reset Stuck Endpoint

| Parameter | Type | Default | Min | Max | Description |
|-----------|------|---------|-----|-----|-------------|
| timeout_minutes | int | 30 | 1 | 1440 | Stuck event timeout |

---

## Common Errors

| Status | Error | Solution |
|--------|-------|----------|
| 401 | Not logged in | Include valid Authorization header |
| 401 | Must be an admin | Use admin account |
| 400 | Invalid parameters | Check parameter ranges |
| 500 | Processing failed | Check server logs |

---

## Quick Scripts

### Bash: Process if Queue Has Items
```bash
#!/bin/bash
TOKEN=$(curl -s -X POST https://your-api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"pass"}' | jq -r '.accessToken')

PENDING=$(curl -s -X GET https://your-api/queue/stats \
  -H "Authorization: Bearer $TOKEN" | jq -r '.stats.pending')

if [ "$PENDING" -gt 0 ]; then
  echo "Processing $PENDING pending events"
  curl -X POST https://your-api/queue/process \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "{\"batch_size\":$PENDING,\"iterations\":1}"
fi
```

### Python: Monitor and Process
```python
#!/usr/bin/env python3
import requests
import time

API = "https://your-api"
CREDS = {"username": "admin", "password": "pass"}

def process_if_pending():
    # Auth
    token = requests.post(f"{API}/auth", json=CREDS).json()['accessToken']
    headers = {'Authorization': f'Bearer {token}'}

    # Check stats
    stats = requests.get(f"{API}/queue/stats", headers=headers).json()
    pending = stats['stats']['pending']

    if pending > 0:
        print(f"Processing {pending} events")
        result = requests.post(f"{API}/queue/process",
            headers=headers,
            json={'batch_size': min(pending, 50), 'iterations': 1}
        ).json()
        print(f"Processed: {result['processed_events']}")
    else:
        print("No pending events")

if __name__ == "__main__":
    while True:
        try:
            process_if_pending()
        except Exception as e:
            print(f"Error: {e}")
        time.sleep(300)  # Every 5 minutes
```

---

## Files Modified

```
api/controllers/webhook_processor.controller.php  [NEW]
api/index.php                                      [MODIFIED - 1 line]
api/routes.php                                     [MODIFIED - 3 lines]
```

---

## Documentation

- **Full API Docs:** `REMOTE_WEBHOOK_PROCESSING_API.md`
- **Implementation:** `IMPLEMENTATION_SUMMARY_REMOTE_API.md`
- **Quick Reference:** `QUICK_REFERENCE_REMOTE_API.md` (this file)
