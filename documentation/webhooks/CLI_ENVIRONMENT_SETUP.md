# CLI Environment Variables Setup Guide

## Problem

When running PHP CLI scripts (like `process_webhooks.php`), environment variables set in Apache's `.htaccess` or `httpd.conf` are **not available**. The script needs these variables to connect to the database and access GoCardless APIs.

## Required Environment Variables

Your CLI script needs access to:
- `KA_DB_USER` - Database username
- `KA_DB_PASSWORD` - Database password
- `KA_MEMBER_KEY` - JWT secret key (32 characters)
- `GOCARDLESS_WEBHOOK_SECRET` - GoCardless webhook secret
- `GOCARDLESS_ACCESS_TOKEN` - GoCardless API access token
- `EMAIL_PASSWORD` - Email account password (if using email features)

---

## Solution 1: .env File (Recommended)

I've updated your CLI script to automatically load environment variables from a `.env` file.

### Setup Steps

#### 1. On Your Production Server

```bash
cd /path/to/api

# Copy the example file
cp .env.production.example .env

# Edit with your actual values
nano .env
```

#### 2. Fill in Your Values

```bash
# Database credentials
KA_DB_USER=your_actual_db_user
KA_DB_PASSWORD=your_actual_db_password

# JWT token secret (must match .htaccess)
KA_MEMBER_KEY=your_32_character_secret_key

# GoCardless credentials
GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret
GOCARDLESS_ACCESS_TOKEN=your_access_token

# Email password
EMAIL_PASSWORD=your_email_password
```

#### 3. Secure the File

```bash
# Make sure only your user can read it
chmod 600 .env

# Verify permissions
ls -la .env
# Should show: -rw------- 1 youruser yourgroup
```

#### 4. Add to .gitignore

```bash
echo ".env" >> .gitignore
```

#### 5. Test It

```bash
php cli/process_webhooks.php --stats
```

### How It Works

The CLI script now includes this at the top:
```php
// Load environment variables for CLI (not available from .htaccess)
require_once __DIR__ . '/env_loader.php';
```

The `env_loader.php` reads `/path/to/api/.env` and makes variables available via `getenv()`.

---

## Solution 2: Shell Script Wrapper

If you prefer not to use a `.env` file, create a wrapper script:

### Create Wrapper Script

```bash
nano ~/bin/process-webhooks.sh
```

```bash
#!/bin/bash

# Set environment variables
export KA_DB_USER="your_db_user"
export KA_DB_PASSWORD="your_db_password"
export KA_MEMBER_KEY="your_32_char_key"
export GOCARDLESS_WEBHOOK_SECRET="your_webhook_secret"
export GOCARDLESS_ACCESS_TOKEN="your_access_token"
export EMAIL_PASSWORD="your_email_password"

# Run the PHP script with all arguments passed through
php /path/to/api/cli/process_webhooks.php "$@"
```

### Make Executable

```bash
chmod +x ~/bin/process-webhooks.sh
```

### Use It

```bash
# Instead of:
php cli/process_webhooks.php --once

# Use:
~/bin/process-webhooks.sh --once
```

### For Cron

```cron
*/5 * * * * /home/youruser/bin/process-webhooks.sh --once >> /var/log/webhooks.log 2>&1
```

---

## Solution 3: Systemd Service (For Continuous Processing)

Best for running the processor as a background service.

### Create Service File

```bash
sudo nano /etc/systemd/system/webhook-processor.service
```

```ini
[Unit]
Description=GoCardless Webhook Queue Processor
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/path/to/api

# Set environment variables
Environment="KA_DB_USER=your_db_user"
Environment="KA_DB_PASSWORD=your_db_password"
Environment="KA_MEMBER_KEY=your_32_char_key"
Environment="GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret"
Environment="GOCARDLESS_ACCESS_TOKEN=your_access_token"
Environment="EMAIL_PASSWORD=your_email_password"

# Run the script
ExecStart=/usr/bin/php /path/to/api/cli/process_webhooks.php --batch-size=10 --sleep=10

# Restart on failure
Restart=always
RestartSec=10

# Logging
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

### Enable and Start

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable on boot
sudo systemctl enable webhook-processor

# Start now
sudo systemctl start webhook-processor

# Check status
sudo systemctl status webhook-processor

# View logs
sudo journalctl -u webhook-processor -f
```

### Manage Service

```bash
# Stop
sudo systemctl stop webhook-processor

# Restart
sudo systemctl restart webhook-processor

# Disable
sudo systemctl disable webhook-processor
```

---

## Solution 4: Supervisord (Alternative to Systemd)

### Install Supervisord

```bash
sudo apt-get install supervisor
```

### Create Config

```bash
sudo nano /etc/supervisor/conf.d/webhook-processor.conf
```

```ini
[program:webhook-processor]
command=/usr/bin/php /path/to/api/cli/process_webhooks.php --batch-size=10 --sleep=10
directory=/path/to/api
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/webhook-processor.log
environment=KA_DB_USER="your_db_user",KA_DB_PASSWORD="your_db_password",KA_MEMBER_KEY="your_key",GOCARDLESS_WEBHOOK_SECRET="your_secret",GOCARDLESS_ACCESS_TOKEN="your_token",EMAIL_PASSWORD="your_password"
```

### Enable and Start

```bash
# Reload config
sudo supervisorctl reread
sudo supervisorctl update

# Start
sudo supervisorctl start webhook-processor

# Check status
sudo supervisorctl status webhook-processor

# View logs
tail -f /var/log/webhook-processor.log
```

---

## Solution 5: Export in Shell Profile

For manual CLI usage (not recommended for production services).

### Add to ~/.bashrc or ~/.bash_profile

```bash
nano ~/.bashrc
```

```bash
# GoCardless Webhook Processor Environment
export KA_DB_USER="your_db_user"
export KA_DB_PASSWORD="your_db_password"
export KA_MEMBER_KEY="your_32_char_key"
export GOCARDLESS_WEBHOOK_SECRET="your_webhook_secret"
export GOCARDLESS_ACCESS_TOKEN="your_access_token"
export EMAIL_PASSWORD="your_email_password"
```

### Reload

```bash
source ~/.bashrc
```

### Test

```bash
php cli/process_webhooks.php --stats
```

**Note:** This only works when you're logged in as that user. Services (cron, systemd) won't have these variables.

---

## Recommended Setup by Use Case

### For Cron Jobs
✅ **Use Solution 1 (.env file)** or Solution 2 (wrapper script)

### For Continuous Background Processing
✅ **Use Solution 3 (systemd)** or Solution 4 (supervisord)

### For Manual Testing
✅ **Use Solution 1 (.env file)**

---

## Security Best Practices

1. **Never commit .env to git**
   ```bash
   echo ".env" >> .gitignore
   ```

2. **Restrict file permissions**
   ```bash
   chmod 600 .env
   ```

3. **Use different credentials per environment**
   - Development: sandbox API keys
   - Production: live API keys

4. **Rotate secrets regularly**
   - Change passwords periodically
   - Regenerate API tokens when needed

5. **Keep secrets in one place**
   - Either `.env` OR `.htaccess`, not both
   - Document which method you're using

---

## Troubleshooting

### Issue: "Connection error: Unable to connect"

**Cause:** Database credentials not loaded

**Fix:**
```bash
# Test if variables are loaded
php -r "require 'cli/env_loader.php'; echo getenv('KA_DB_USER');"

# Should output your database username
```

### Issue: ".env file not found"

**Cause:** File doesn't exist or wrong path

**Fix:**
```bash
# Check file exists
ls -la /path/to/api/.env

# Check current directory
pwd

# Create file from example
cp .env.example .env
nano .env
```

### Issue: "Invalid signature" errors

**Cause:** `GOCARDLESS_WEBHOOK_SECRET` not loaded

**Fix:**
```bash
# Verify the secret is loaded
php -r "require 'cli/env_loader.php'; echo getenv('GOCARDLESS_WEBHOOK_SECRET');"
```

### Issue: Cron job fails but manual run works

**Cause:** Cron doesn't load user environment

**Fix:** Use absolute paths in cron:
```cron
*/5 * * * * cd /full/path/to/api && /usr/bin/php cli/process_webhooks.php --once
```

---

## Files Created

1. `api/cli/env_loader.php` - Loads environment variables from .env file
2. `api/.env.example` - Template for development
3. `api/.env.production.example` - Template for production

## Files Modified

1. `api/cli/process_webhooks.php` - Added env_loader.php include

---

## Quick Start for Production

```bash
# 1. SSH to production server
ssh user@your-server.com

# 2. Navigate to API directory
cd /path/to/api

# 3. Create .env file
cp .env.production.example .env

# 4. Edit with actual values (use your preferred editor)
nano .env

# 5. Secure the file
chmod 600 .env

# 6. Test it
php cli/process_webhooks.php --stats

# 7. If using cron, add job
crontab -e
# Add: */5 * * * * cd /path/to/api && php cli/process_webhooks.php --once
```

---

## Summary

The CLI script now automatically loads environment variables from `.env` file, making it work outside of Apache's environment. This is the standard approach used by most PHP applications (Laravel, Symfony, etc.).

For production, I recommend:
- **Cron usage:** .env file (Solution 1)
- **Continuous service:** systemd with Environment directives (Solution 3)
