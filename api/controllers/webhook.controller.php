<?php

namespace Controllers;

class WebhookCtl {

    /**
     * Process GoCardless webhook
     * Handles signature validation, idempotency, and event processing
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

        // Validate signature
        if (!$webhook->validateSignature($payload, $signature)) {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid signature"));
            error_log("GoCardless webhook signature validation failed");
            return;
        }

        // Parse JSON payload
        $webhook_data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid JSON"));
            error_log("GoCardless webhook JSON parse error: " . json_last_error_msg());
            return;
        }

        // Process events
        try {
            $results = $webhook->processEvents($webhook_data);

            // Return success response
            http_response_code(200);
            echo json_encode(array(
                "message" => "Webhook processed successfully",
                "results" => $results
            ));
        } catch (\Exception $e) {
            // Log error but return 200 to prevent retries for unrecoverable errors
            error_log("GoCardless webhook processing error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(array(
                "message" => "Error processing webhook",
                "error" => $e->getMessage()
            ));
        }
    }
}
