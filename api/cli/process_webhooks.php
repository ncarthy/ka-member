<?php
/**
 * CLI script to process webhook queue
 *
 * Usage:
 *   php process_webhooks.php [options]
 *
 * Options:
 *   --batch-size=N      Number of events to process per batch (default: 10)
 *   --sleep=N           Seconds to sleep between batches (default: 10)
 *   --iterations=N      Maximum iterations (default: 0 = infinite)
 *   --once              Process one batch and exit (same as --iterations=1)
 *   --reset-stuck       Reset stuck events and exit
 *   --stats             Show queue statistics and exit
 *   --help              Show this help message
 *
 * Examples:
 *   php process_webhooks.php                    # Run continuously
 *   php process_webhooks.php --once             # Process one batch
 *   php process_webhooks.php --iterations=5     # Run 5 iterations
 *   php process_webhooks.php --reset-stuck      # Reset stuck events
 *   php process_webhooks.php --stats            # Show statistics
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("Error: This script must be run from the command line.\n");
}

// Include necessary files
require_once dirname(__DIR__) . '/core/database.php';
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/models/webhook_queue.php';
require_once dirname(__DIR__) . '/models/webhook_log.php';
require_once dirname(__DIR__) . '/models/gocardless_webhook.php';
require_once dirname(__DIR__) . '/models/member.php';
require_once dirname(__DIR__) . '/models/member_name.php';
require_once dirname(__DIR__) . '/models/subscription.php';
require_once dirname(__DIR__) . '/models/country.php';
require_once dirname(__DIR__) . '/webhook_handlers/abstract_webhook_handler.php';
require_once dirname(__DIR__) . '/webhook_handlers/mandate_created_handler.php';
require_once dirname(__DIR__) . '/webhook_handlers/payment_created_handler.php';
require_once dirname(__DIR__) . '/webhook_handlers/subscription_created_handler.php';
require_once dirname(__DIR__) . '/webhook_handlers/subscription_terminated_handler.php';
require_once dirname(__DIR__) . '/workers/webhook_queue_processor.php';

// Load composer autoload for GoCardless library
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} else {
    die("Error: Composer dependencies not installed. Run: composer install\n");
}

// Parse command line options
$options = getopt('', [
    'batch-size:',
    'sleep:',
    'iterations:',
    'once',
    'reset-stuck',
    'stats',
    'help'
]);

// Show help
if (isset($options['help'])) {
    showHelp();
    exit(0);
}

try {
    $processor = new \Workers\WebhookQueueProcessor();

    // Handle special commands
    if (isset($options['stats'])) {
        $processor->displayStats();
        exit(0);
    }

    if (isset($options['reset-stuck'])) {
        $processor->resetStuckEvents();
        exit(0);
    }

    // Get processing options
    $batch_size = isset($options['batch-size']) ? (int)$options['batch-size'] : 10;
    $sleep_seconds = isset($options['sleep']) ? (int)$options['sleep'] : 10;
    $max_iterations = 0;

    if (isset($options['once'])) {
        $max_iterations = 1;
    } elseif (isset($options['iterations'])) {
        $max_iterations = (int)$options['iterations'];
    }

    // Validate options
    if ($batch_size < 1 || $batch_size > 100) {
        die("Error: batch-size must be between 1 and 100\n");
    }       

    if ($sleep_seconds < 0 || $sleep_seconds > 3600) {
        die("Error: sleep must be between 0 and 3600 seconds\n");
    }

    // Run processor
    $processor->run($batch_size, $sleep_seconds, $max_iterations);

} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Display help message
 */
function showHelp() {
    echo <<<HELP

GoCardless Webhook Queue Processor
===================================

Process queued webhook events from the webhook_queue table.

Usage:
  php process_webhooks.php [options]

Options:
  --batch-size=N      Number of events to process per batch (default: 10)
  --sleep=N           Seconds to sleep between batches (default: 10)
  --iterations=N      Maximum iterations (default: 0 = infinite)
  --once              Process one batch and exit (same as --iterations=1)
  --reset-stuck       Reset stuck events and exit
  --stats             Show queue statistics and exit
  --help              Show this help message

Examples:

  # Run continuously (Ctrl+C to stop)
  php process_webhooks.php

  # Process one batch and exit
  php process_webhooks.php --once

  # Run 5 iterations with custom batch size
  php process_webhooks.php --iterations=5 --batch-size=20

  # Process with faster polling
  php process_webhooks.php --sleep=5

  # Reset stuck events (been processing for >30 minutes)
  php process_webhooks.php --reset-stuck

  # Show queue statistics
  php process_webhooks.php --stats

Setup:

  1. Ensure webhook_queue table exists in database
  2. Configure GoCardless credentials in environment
  3. Run this script via cron or supervisord for production

Cron Example (run every minute):
  * * * * * cd /path/to/api && php cli/process_webhooks.php --once

Supervisord Example:
  [program:webhook_processor]
  command=/usr/bin/php /path/to/api/cli/process_webhooks.php
  autostart=true
  autorestart=true
  stderr_logfile=/var/log/webhook_processor.err.log
  stdout_logfile=/var/log/webhook_processor.out.log


HELP;
}
