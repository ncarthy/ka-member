<?php
/**
 * GoCardless Webhook Endpoint
 *
 * Receives webhook events from GoCardless for:
 * - mandates.created: Creates new member records
 * - payments.confirmed: Creates transaction records
 *
 * Security: HMAC-SHA256 signature validation (bypasses JWT auth)
 * Idempotency: Unique constraint on webhook_id in database
 */

// Get raw POST payload (needed for signature validation)
$payload = file_get_contents('php://input');

// Get signature from header
$signature = $_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '';

// Process webhook through controller
\Controllers\WebhookCtl::processGoCardless($payload, $signature);
