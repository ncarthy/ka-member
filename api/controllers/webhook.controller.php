<?php

namespace Controllers;

use \GoCardlessPro\Core\Exception\InvalidSignatureException;

class WebhookCtl {

    /**
     * Process GoCardless webhook
     * Handles signature validation, idempotency, and event processing
     * Uses GoCardless PHP library for parsing and validation
     *
     * @param string $payload Raw JSON payload
     * @param string $signature Webhook-Signature header value
     */
    public static function processGoCardless($payload, $signature) {
        // Validate inputs
        if (empty($payload)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing payload"));
            return;
        }

        if (empty($signature)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing signature"));
            return;
        }

        // Initialize webhook model
        $webhook = new \Models\GoCardlessWebhook();

        // Parse and validate webhook using GoCardless library
        try {
            $events = $webhook->parseWebhook($payload, $signature);
        } catch (InvalidSignatureException $e) {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid signature"));
            error_log("GoCardless webhook signature validation failed: " . $e->getMessage());
            return;
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid webhook data"));
            error_log("GoCardless webhook parsing error: " . $e->getMessage());
            return;
        }

        // Enqueue events for async processing
        try {
            $webhook_queue = new \Models\WebhookQueue();
            $enqueued_count = 0;
            $skipped_count = 0;

            // Parse the payload to get raw event data
            $payload_data = json_decode($payload, true);

            if (!isset($payload_data['events']) || !is_array($payload_data['events'])) {
                throw new \Exception("Invalid webhook payload: missing events array");
            }

            foreach ($events as $index => $event) {
                // Check if event already exists in queue (idempotency)
                if ($webhook_queue->exists($event->id)) {
                    error_log("Event {$event->id} already exists in queue, skipping");
                    $skipped_count++;
                    continue;
                }

                // Get raw event data for this event
                $raw_event = isset($payload_data['events'][$index])
                    ? json_encode($payload_data['events'][$index])
                    : '{}';

                // Enqueue the event
                $webhook_queue->event_id = $event->id;
                $webhook_queue->resource_type = $event->resource_type;
                $webhook_queue->action = $event->action;
                $webhook_queue->payload = json_encode([
                    'links' => (array)($event->links ?? new \stdClass()),
                    'details' => (array)($event->details ?? new \stdClass()),
                    'metadata' => (array)($event->metadata ?? new \stdClass())
                ]);
                $webhook_queue->raw_payload = $raw_event;
                $webhook_queue->max_retries = 3; // Default retry limit

                if ($webhook_queue->enqueue()) {
                    $enqueued_count++;
                    error_log("Enqueued event {$event->id} ({$event->resource_type}.{$event->action})");
                } else {
                    error_log("Failed to enqueue event {$event->id}");
                }
            }

            // Return success response
            http_response_code(200);
            echo json_encode(array(
                "message" => "Webhook received and queued successfully",
                "enqueued" => $enqueued_count,
                "skipped" => $skipped_count
            ));
        } catch (\Exception $e) {
            // Log error but return 200 to prevent retries (already validated signature)
            error_log("GoCardless webhook queueing error: " . $e->getMessage());
            http_response_code(200);
            echo json_encode(array(
                "message" => "Webhook received but queueing failed",
                "error" => $e->getMessage()
            ));
        }
    }
}
