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

        // Process events
        try {
            $results = $webhook->processEvents($events);

            // Return success response
            http_response_code(200);
            echo json_encode(array(
                "message" => "Webhook processed successfully",
                "results" => $results
            ));
        } catch (\Exception $e) {
            // Log error but return 500 for processing errors
            error_log("GoCardless webhook processing error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(array(
                "message" => "Error processing webhook",
                "error" => $e->getMessage()
            ));
        }
    }
}
