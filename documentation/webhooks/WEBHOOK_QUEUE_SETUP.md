# GoCardless Webhook Queue System - Setup Guide

## Overview

The webhook queue system provides asynchronous, reliable processing of GoCardless webhook events. Events are queued immediately upon receipt and processed by a background worker, providing:

- **Fast webhook responses** (< 1 second) to prevent GoCardless timeouts
- **Automatic retry logic** with exponential backoff (2, 4, 8 minutes)
- **Idempotency** to prevent duplicate processing
- **Resilience** against transient failures
- **Monitoring** with queue statistics

## Architecture

```
GoCardless → Webhook Endpoint → Queue → Background Worker → Event Handlers
                ↓                  ↓            ↓                  ↓
           Validate Sig      webhook_queue    Fetch Events    Process Events
           Enqueue Events    Table (MySQL)    Mark Status     Update Database
           Return 200 OK
```

## Database Setup

### 1. Create webhook_queue Table

Run the SQL script to create the queue table:

```bash
mysql -u username -p database_name < F:\claude\gocardless\webhook_queue_table.sql
```

Or manually in MySQL:

```sql
CREATE TABLE `webhook_queue` (
  `idwebhook_queue` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` VARCHAR(255) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `payload` TEXT NOT NULL,
  `raw_payload` TEXT NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `retry_count` INT UNSIGNED DEFAULT 0,
  `max_retries` INT UNSIGNED DEFAULT 3,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processing_at` TIMESTAMP NULL,
  `completed_at` TIMESTAMP NULL,
  `next_retry_at` TIMESTAMP NULL,
  UNIQUE KEY `unique_event_id` (`event_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_next_retry` (`status`, `next_retry_at`),
  INDEX `idx_resource_action` (`resource_type`, `action`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Verify Table Creation

```sql
SHOW TABLES LIKE 'webhook_queue';
DESCRIBE webhook_queue;
```

## Configuration

Ensure your environment variables are configured:

```bash
# Apache/PHP environment
GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret_here
GOCARDLESS_ACCESS_TOKEN=your_access_token_here
GOCARDLESS_ENVIRONMENT=sandbox  # or 'live' for production
```

## Testing the Queue System

### 1. Manual Queue Testing

Create a test script to enqueue events:

```php
<?php
require_once 'core/database.php';
require_once 'models/webhook_queue.php';

$queue = new \Models\WebhookQueue();

// Test event
$queue->event_id = 'TEST_' . time();
$queue->resource_type = 'mandates';
$queue->action = 'created';
$queue->payload = json_encode([
    'links' => ['mandate' => 'MD000123'],
    'details' => [],
    'metadata' => []
]);
$queue->raw_payload = '{"test": true}';
$queue->max_retries = 3;

if ($queue->enqueue()) {
    echo "Event queued successfully: {$queue->id}\n";
} else {
    echo "Failed to enqueue event\n";
}
?>
```

### 2. Check Queue Statistics

```bash
php F:\source\repos\ka-member\api\cli\process_webhooks.php --stats
```

Expected output:
```
=== Webhook Queue Statistics ===
Pending:    1
Processing: 0
Completed:  0
Failed:     0
================================
```

### 3. Process One Batch

```bash
php F:\source\repos\ka-member\api\cli\process_webhooks.php --once
```

Expected output:
```
Starting webhook queue processor...
Batch size: 10, Sleep: 10s
Max iterations: 1

--- Iteration 1 ---
Fetching up to 10 pending events...
Found 1 pending events.

Processing event TEST_1234567890 (queue ID: 1)...
Executing handler for mandates.created...
Event TEST_1234567890 processed successfully.

Batch results: 1 processed, 0 failed, 0 skipped
Webhook queue processor stopped.
```

### 4. Test Webhook Receipt

Send a test webhook to your endpoint:

```bash
curl -X POST http://localhost/api/webhook/gocardless \
  -H "Content-Type: application/json" \
  -H "Webhook-Signature: v1:test_signature" \
  -d '{
    "events": [{
      "id": "EV123",
      "resource_type": "mandates",
      "action": "created",
      "links": {"mandate": "MD000123"}
    }]
  }'
```

Check the queue:
```bash
php F:\source\repos\ka-member\api\cli\process_webhooks.php --stats
```

Process the event:
```bash
php F:\source\repos\ka-member\api\cli\process_webhooks.php --once
```

## Production Deployment

### Option 1: Cron Job (Recommended for Low Volume)

Add to crontab to run every minute:

```cron
* * * * * cd /path/to/api && php cli/process_webhooks.php --once >> /var/log/webhook_processor.log 2>&1
```

**Pros:**
- Simple setup
- No daemon management
- Automatically restarts on failure

**Cons:**
- 1-minute minimum delay
- Not suitable for high volume

### Option 2: Supervisord (Recommended for Production)

Install supervisord:
```bash
sudo apt-get install supervisor
```

Create config file `/etc/supervisor/conf.d/webhook_processor.conf`:

```ini
[program:webhook_processor]
command=/usr/bin/php /path/to/api/cli/process_webhooks.php --batch-size=20 --sleep=5
directory=/path/to/api
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/webhook_processor.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=10
environment=GOCARDLESS_WEBHOOK_SECRET="your_secret",GOCARDLESS_ACCESS_TOKEN="your_token"
```

Start the processor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start webhook_processor
```

Check status:
```bash
sudo supervisorctl status webhook_processor
```

View logs:
```bash
sudo tail -f /var/log/webhook_processor.log
```

**Pros:**
- Continuous processing
- Auto-restart on crash
- Better for high volume
- Process monitoring

**Cons:**
- More complex setup
- Requires supervisord installation

### Option 3: systemd Service

Create service file `/etc/systemd/system/webhook-processor.service`:

```ini
[Unit]
Description=GoCardless Webhook Queue Processor
After=mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/api
ExecStart=/usr/bin/php /path/to/api/cli/process_webhooks.php --batch-size=20 --sleep=5
Restart=always
RestartSec=5
Environment="GOCARDLESS_WEBHOOK_SECRET=your_secret"
Environment="GOCARDLESS_ACCESS_TOKEN=your_token"

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable webhook-processor
sudo systemctl start webhook-processor
```

Check status:
```bash
sudo systemctl status webhook-processor
```

View logs:
```bash
sudo journalctl -u webhook-processor -f
```

## CLI Command Reference

### Basic Usage

```bash
# Run continuously (Ctrl+C to stop)
php cli/process_webhooks.php

# Process one batch and exit
php cli/process_webhooks.php --once

# Run 5 iterations
php cli/process_webhooks.php --iterations=5

# Custom batch size and sleep time
php cli/process_webhooks.php --batch-size=20 --sleep=5
```

### Monitoring

```bash
# Show queue statistics
php cli/process_webhooks.php --stats

# Reset stuck events (processing > 30 minutes)
php cli/process_webhooks.php --reset-stuck
```

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--batch-size=N` | Events to process per batch | 10 |
| `--sleep=N` | Seconds between batches | 10 |
| `--iterations=N` | Max iterations (0=infinite) | 0 |
| `--once` | Process one batch and exit | - |
| `--reset-stuck` | Reset stuck events and exit | - |
| `--stats` | Show statistics and exit | - |
| `--help` | Show help message | - |

## Monitoring and Maintenance

### Check Queue Status

Run periodically to monitor queue health:

```bash
php cli/process_webhooks.php --stats
```

### Monitor Failed Events

Query the database for permanently failed events:

```sql
SELECT
    event_id,
    resource_type,
    action,
    error_message,
    retry_count,
    created_at
FROM webhook_queue
WHERE status = 'failed'
ORDER BY created_at DESC
LIMIT 20;
```

### Reset Stuck Events

If events are stuck in 'processing' status (e.g., processor crashed):

```bash
php cli/process_webhooks.php --reset-stuck
```

This resets any event that's been processing for more than 30 minutes.

### Manually Retry Failed Events

To manually retry a failed event:

```sql
UPDATE webhook_queue
SET status = 'pending',
    retry_count = 0,
    next_retry_at = NULL,
    error_message = NULL
WHERE event_id = 'EV123';
```

### Clean Up Old Events

Periodically archive or delete completed events:

```sql
-- Archive completed events older than 90 days
INSERT INTO webhook_queue_archive
SELECT * FROM webhook_queue
WHERE status = 'completed'
  AND completed_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Delete archived events
DELETE FROM webhook_queue
WHERE status = 'completed'
  AND completed_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Troubleshooting

### Events Not Being Processed

1. **Check if processor is running:**
   ```bash
   ps aux | grep process_webhooks
   ```

2. **Check for pending events:**
   ```bash
   php cli/process_webhooks.php --stats
   ```

3. **Try processing manually:**
   ```bash
   php cli/process_webhooks.php --once
   ```

4. **Check processor logs:**
   ```bash
   tail -f /var/log/webhook_processor.log
   ```

### High Failure Rate

1. **Check error messages:**
   ```sql
   SELECT error_message, COUNT(*) as count
   FROM webhook_queue
   WHERE status = 'failed'
   GROUP BY error_message
   ORDER BY count DESC;
   ```

2. **Check GoCardless API connectivity:**
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        https://api-sandbox.gocardless.com/mandates/MD000123
   ```

3. **Check database connectivity:**
   ```sql
   SELECT 1;
   ```

### Events Stuck in Processing

Run the reset command:
```bash
php cli/process_webhooks.php --reset-stuck
```

Or manually:
```sql
UPDATE webhook_queue
SET status = 'pending',
    processing_at = NULL,
    next_retry_at = NOW()
WHERE status = 'processing'
  AND processing_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);
```

### Memory Issues

If processing large volumes, adjust PHP memory limit:

```bash
php -d memory_limit=512M cli/process_webhooks.php
```

Or in php.ini:
```ini
memory_limit = 512M
```

## Performance Tuning

### For High Volume

```bash
# Increase batch size and reduce sleep time
php cli/process_webhooks.php --batch-size=50 --sleep=2
```

### For Low Volume

```bash
# Decrease batch size and increase sleep time
php cli/process_webhooks.php --batch-size=5 --sleep=30
```

### Multiple Workers

Run multiple processor instances in parallel (be careful with database locks):

```bash
# Worker 1
php cli/process_webhooks.php --batch-size=10 &

# Worker 2
php cli/process_webhooks.php --batch-size=10 &
```

Note: MySQL row-level locking prevents race conditions when marking events as processing.

## Security Notes

1. **Webhook signature validation** happens before queueing (in webhook controller)
2. **Queue entries are trusted** - they've already passed signature validation
3. **Protect the CLI script** from web access (put in cli/ directory outside webroot if possible)
4. **Store credentials securely** in environment variables, not in code
5. **Use HTTPS** for webhook endpoint in production

## Migration from Synchronous Processing

If migrating from the original synchronous webhook processing:

1. **Create the queue table** (see Database Setup)
2. **Deploy updated code** (webhook controller now enqueues)
3. **Start the processor** (cron or supervisord)
4. **Monitor both systems** for a transition period
5. **Gradually increase queue usage** by adjusting batch size

The queue system is backwards-compatible - the same event handlers are used.

## Support

For issues or questions:
1. Check error logs: `/var/log/webhook_processor.log`
2. Check PHP error logs: `/var/log/php_errors.log`
3. Check queue statistics: `php cli/process_webhooks.php --stats`
4. Check database: `SELECT * FROM webhook_queue WHERE status = 'failed' LIMIT 10;`
