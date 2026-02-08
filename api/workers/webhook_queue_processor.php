<?php
namespace Workers;

use \Models\WebhookQueue;
use \Models\WebhookLog;
use \Models\GoCardlessWebhook;

/**
 * Background worker to process queued webhook events
 * Fetches pending events from webhook_queue and processes them using existing handlers
 */
class WebhookQueueProcessor {

    private $conn;
    private $client;
    private $webhook_queue;
    private $gocardless_webhook;

    public function __construct() {
        $this->conn = \Core\Database::getInstance()->conn;

        // Initialize GoCardless client
        $access_token = \Core\Config::read('gocardless.access_token');
        $environment = \Core\Config::read('gocardless.environment') ?? 'sandbox';

        $this->client = new \GoCardlessPro\Client([
            'access_token' => $access_token,
            'environment' => $environment === 'live' ? \GoCardlessPro\Environment::LIVE : \GoCardlessPro\Environment::SANDBOX
        ]);

        $this->webhook_queue = new WebhookQueue();
        $this->gocardless_webhook = new GoCardlessWebhook();
    }

    /**
     * Process pending events from the queue
     * @param int $batch_size Number of events to process in this batch
     * @return array Statistics about processed events
     */
    public function processPendingEvents($batch_size = 10) {
        $stats = [
            'processed' => 0,
            'failed' => 0,
            'skipped' => 0
        ];

        echo "Fetching up to $batch_size pending events...\n";
        $events = $this->webhook_queue->fetchPendingEvents($batch_size);

        if (empty($events)) {
            echo "No pending events found.\n";
            return $stats;
        }

        echo "Found " . count($events) . " pending events.\n";

        foreach ($events as $queued_event) {
            $event_id = $queued_event['event_id'];
            $queue_id = $queued_event['id'];

            echo "\nProcessing event $event_id (queue ID: $queue_id)...\n";

            // Mark as processing
            if (!$this->webhook_queue->markAsProcessing($queue_id)) {
                echo "Failed to mark event $event_id as processing, skipping.\n";
                $stats['skipped']++;
                continue;
            }

            try {
                // Reconstruct event object from queue data
                $event = $this->reconstructEventObject($queued_event);

                // Create webhook log entry for this processing attempt
                $webhook_log = new WebhookLog();
                $webhook_log->webhook_id = "queue_retry_{$queue_id}_" . time();
                $webhook_log->event_id = $event_id;
                $webhook_log->resource_type = $queued_event['resource_type'];
                $webhook_log->action = $queued_event['action'];
                $webhook_log->payload = $queued_event['raw_payload'];
                $webhook_log->processed = 0;

                if (!$webhook_log->create()) {
                    throw new \Exception("Failed to create webhook log entry");
                }

                // Get appropriate handler
                $handler = $this->gocardless_webhook->getHandlerForEvent(
                    $queued_event['resource_type'],
                    $queued_event['action']
                );

                if (!$handler) {
                    // No handler for this event type - mark as completed (not failed)
                    $error_message = "No handler found for {$queued_event['resource_type']}.{$queued_event['action']}";
                    echo "$error_message\n";
                    error_log($error_message);

                    // Update webhook_queue with error message but mark as completed
                    $update_query = "UPDATE webhook_queue
                                    SET status = 'completed',
                                        completed_at = NOW(),
                                        error_message = :error_message
                                    WHERE idwebhook_queue = :id";
                    $stmt = $this->conn->prepare($update_query);
                    $stmt->bindParam(':id', $queue_id, \PDO::PARAM_INT);
                    $stmt->bindParam(':error_message', $error_message);
                    $stmt->execute();

                    echo "Event $event_id marked as completed (no handler available).\n";
                    $stats['processed']++;
                    continue; // Skip to next event
                }

                // Process the event
                echo "Executing handler for {$queued_event['resource_type']}.{$queued_event['action']}...\n";
                $result = $handler->handle($event, $webhook_log);

                // Mark as completed
                if ($this->webhook_queue->markAsCompleted($queue_id)) {
                    echo "Event $event_id processed successfully.\n";
                    $stats['processed']++;
                } else {
                    echo "Warning: Event processed but failed to mark as completed.\n";
                    $stats['processed']++;
                }

            } catch (\Exception $e) {
                $error_message = $e->getMessage();
                echo "Error processing event $event_id: $error_message\n";

                // Mark as failed (will schedule retry if attempts remain)
                if ($this->webhook_queue->markAsFailed($queue_id, $error_message)) {
                    echo "Event $event_id marked as failed. Will retry if attempts remain.\n";
                } else {
                    echo "Warning: Failed to mark event $event_id as failed.\n";
                }

                $stats['failed']++;
            }
        }

        return $stats;
    }

    /**
     * Reconstruct GoCardless event object from queue data
     * @param array $queued_event
     * @return object Event object compatible with handlers
     */
    private function reconstructEventObject($queued_event) {
        $payload_data = json_decode($queued_event['payload'], true);

        $event = new \stdClass();
        $event->id = $queued_event['event_id'];
        $event->resource_type = $queued_event['resource_type'];
        $event->action = $queued_event['action'];

        // Reconstruct links
        $event->links = new \stdClass();
        if (isset($payload_data['links']) && is_array($payload_data['links'])) {
            foreach ($payload_data['links'] as $key => $value) {
                $event->links->$key = $value;
            }
        }

        // Reconstruct details
        $event->details = new \stdClass();
        if (isset($payload_data['details']) && is_array($payload_data['details'])) {
            foreach ($payload_data['details'] as $key => $value) {
                $event->details->$key = $value;
            }
        }

        // Reconstruct metadata
        $event->metadata = new \stdClass();
        if (isset($payload_data['metadata']) && is_array($payload_data['metadata'])) {
            foreach ($payload_data['metadata'] as $key => $value) {
                $event->metadata->$key = $value;
            }
        }

        return $event;
    }

    /**
     * Reset stuck events that have been processing for too long
     * @param int $timeout_minutes
     * @return int Number of events reset
     */
    public function resetStuckEvents($timeout_minutes = 30) {
        echo "Resetting stuck events (timeout: $timeout_minutes minutes)...\n";
        $count = $this->webhook_queue->resetStuckEvents($timeout_minutes);
        echo "Reset $count stuck events.\n";
        return $count;
    }

    /**
     * Display queue statistics
     */
    public function displayStats() {
        $stats = $this->webhook_queue->getStats();

        echo "\n=== Webhook Queue Statistics ===\n";
        echo "Pending:    {$stats['pending']}\n";
        echo "Processing: {$stats['processing']}\n";
        echo "Completed:  {$stats['completed']}\n";
        echo "Failed:     {$stats['failed']}\n";
        echo "================================\n\n";
    }

    /**
     * Main processing loop - continuously process events
     * @param int $batch_size Number of events to process per batch
     * @param int $sleep_seconds Seconds to sleep between batches
     * @param int $max_iterations Maximum iterations (0 = infinite)
     */
    public function run($batch_size = 10, $sleep_seconds = 10, $max_iterations = 0) {
        $iteration = 0;

        echo "Starting webhook queue processor...\n";
        echo "Batch size: $batch_size, Sleep: {$sleep_seconds}s\n";
        if ($max_iterations > 0) {
            echo "Max iterations: $max_iterations\n";
        } else {
            echo "Running continuously (Ctrl+C to stop)\n";
        }
        echo "\n";

        while (true) {
            $iteration++;

            echo "\n--- Iteration $iteration ---\n";

            // Reset stuck events every 10 iterations
            if ($iteration % 10 === 0) {
                $this->resetStuckEvents();
            }

            // Display stats every 5 iterations
            if ($iteration % 5 === 0) {
                $this->displayStats();
            }

            // Process pending events
            $stats = $this->processPendingEvents($batch_size);

            echo "\nBatch results: {$stats['processed']} processed, {$stats['failed']} failed, {$stats['skipped']} skipped\n";

            // Check if we've reached max iterations
            if ($max_iterations > 0 && $iteration >= $max_iterations) {
                echo "\nReached max iterations ($max_iterations), stopping.\n";
                break;
            }

            // Sleep before next batch
            echo "Sleeping for {$sleep_seconds}s...\n";
            sleep($sleep_seconds);
        }

        echo "\nWebhook queue processor stopped.\n";
    }
}
