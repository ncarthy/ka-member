<?php
namespace Models;
use \PDO;
use \GoCardlessPro\Webhook;
use \GoCardlessPro\Core\Exception\InvalidSignatureException;
use \WebhookHandlers\MandateCreatedHandler;
use \WebhookHandlers\PaymentCreatedHandler;
use \WebhookHandlers\SubscriptionCreatedHandler;
use \WebhookHandlers\SubscriptionTerminatedHandler;

class GoCardlessWebhook {
    private $conn;
    private $webhook_secret;
    private $client;

    // Constants for payment configuration
    const BANK_ID = 5; // Lloyds bank account (receives GoCardless payments)
    const PAYMENT_TYPE_ID = 6; // Direct Debit payment type
    const PENDIND_MEMBERSHIP_STATUS_ID = 7; // Pending membership status ID

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
        $this->webhook_secret = getenv(\Core\Config::read('gocardless.webhook_secret'));
        $access_token = getenv(\Core\Config::read('gocardless.access_token'));
        $this->client = new \GoCardlessPro\Client([
            'access_token' => $access_token,
            'environment'  => \GoCardlessPro\Environment::SANDBOX
        ]);

        if (empty($this->webhook_secret)) {
            error_log('GoCardless webhook secret not configured');
        }
    }

    /**
     * Parse and validate webhook using GoCardless library
     * @param string $payload Raw JSON payload
     * @param string $signature Signature from Webhook-Signature header
     * @return array Parsed events
     * @throws InvalidSignatureException if signature is invalid
     */
    public function parseWebhook($payload, $signature) {
        if (empty($this->webhook_secret)) {
            throw new \Exception('Webhook secret not configured');
        }

        // Use GoCardless library to parse and validate webhook
        return Webhook::parse($payload, $signature, $this->webhook_secret);
    }

    /**
     * Process webhook events
     * @param array $events Array of event objects from GoCardless library
     * @param string $raw_payload Original raw JSON payload for logging
     * @return array Results of processing each event
     */
    public function processEvents($events, $raw_payload) {
        $results = [];

        if (!is_array($events)) {
            error_log('Invalid events data: not an array');
            return ['error' => 'Invalid events data'];
        }

        // Parse raw payload to get original event data
        $payload_data = json_decode($raw_payload, true);
        $payload_events = $payload_data['events'] ?? [];

        foreach ($events as $index => $event) {
            // Get corresponding raw event data for this event
            $raw_event = $payload_events[$index] ?? null;
            $result = $this->processEvent($event, $raw_event);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Process a single event
     * @param object $event Event object from GoCardless library
     * @param array|null $raw_event Original raw event data from webhook payload
     * @return array Result of processing
     */
    private function processEvent($event, $raw_event = null) {
        // Access properties from GoCardless event object
        $event_id = $event->id ?? 'unknown';
        $resource_type = $event->resource_type ?? '';
        $action = $event->action ?? '';

        error_log("Processing event: $event_id - $resource_type.$action");

        // Log webhook to database
        $webhook_log = new WebhookLog();
        $webhook_log->webhook_id = $event_id; // Using event ID as webhook ID for idempotency
        $webhook_log->event_id = $event_id;
        $webhook_log->resource_type = $resource_type;
        $webhook_log->action = $action;
        $webhook_log->resource_id = $event->links->{$resource_type} ?? null;
        // Store the original raw event data instead of trying to serialize the object
        $webhook_log->payload = $raw_event ? json_encode($raw_event) : json_encode(['event_id' => $event_id]);

        // Check idempotency
        if ($webhook_log->exists($event_id)) {
            error_log("Event $event_id already processed - skipping");
            return ['event_id' => $event_id, 'status' => 'duplicate'];
        }

        // Create log entry
        if (!$webhook_log->create()) {
            error_log("Failed to create webhook log for event $event_id");
            return ['event_id' => $event_id, 'status' => 'log_failed'];
        }

        // Route to appropriate handler
        try {
            $handler = $this->getHandlerForEvent($resource_type, $action);

            if ($handler) {
                $result = $handler->handle($event, $webhook_log);
            } else {
                error_log("Unhandled event type: $resource_type.$action");
                $webhook_log->markProcessed();
                $result = ['event_id' => $event_id, 'status' => 'unhandled'];
            }

            return $result;
        } catch (\Exception $e) {
            error_log("Error processing event $event_id: " . $e->getMessage());
            $webhook_log->recordError($e->getMessage());
            return ['event_id' => $event_id, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get the appropriate handler for an event
     * @param string $resource_type
     * @param string $action
     * @return \WebhookHandlers\AbstractWebhookHandler|null
     */
    private function getHandlerForEvent($resource_type, $action) {
        if ($resource_type === 'mandates' && $action === 'created') {
            return new MandateCreatedHandler($this->conn, $this->client);
        } elseif ($resource_type === 'payments' && $action === 'confirmed') {
            return new PaymentCreatedHandler($this->conn, $this->client);
        } elseif ($resource_type === 'subscriptions' && $action === 'created') {
            return new SubscriptionCreatedHandler($this->conn, $this->client);
        } elseif ($resource_type === 'subscriptions' && in_array($action, ['cancelled', 'failed', 'expired'])) {
            return new SubscriptionTerminatedHandler($this->conn, $this->client);
        }

        return null;
    }
}
