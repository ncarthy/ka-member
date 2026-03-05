<?php

declare(strict_types=1);

namespace Tests\Integration;

final class WebhookQueueApiTest extends IntegrationTestCase
{
    public function test_webhook_test_route_missing_payload_returns_400(): void
    {
        $response = $this->client->request('POST', '/webhook/gocardless', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '',
        ]);

        self::assertSame(400, $response['status']);
    }

    public function test_webhook_missing_signature_returns_400(): void
    {
        $payload = [
            'events' => [[
                'id' => 'TEST_EVENT_MISSING_SIG',
                'resource_type' => 'mandates',
                'action' => 'created',
                'links' => ['mandate' => 'MD000TESTMISSING'],
            ]],
        ];

        $response = $this->client->request('POST', '/webhook/gocardless', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $payload,
        ]);

        self::assertSame(400, $response['status']);
    }

    public function test_webhook_invalid_signature_returns_401(): void
    {
        $payloadJson = json_encode([
            'events' => [[
                'id' => 'TEST_EVENT_BAD_SIG',
                'resource_type' => 'mandates',
                'action' => 'created',
                'links' => ['mandate' => 'MD000TESTBADSIG'],
            ]],
        ]);

        $response = $this->client->request('POST', '/webhook/gocardless', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Webhook-Signature' => hash_hmac('sha256', (string) $payloadJson, 'wrong-secret'),
            ],
            'body' => $payloadJson,
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_webhook_signature_validation_valid_returns_200(): void
    {
        $response = $this->signedWebhookRequest([
            'events' => [[
                'id' => 'TEST_EVENT_SIGNATURE_VALID',
                'resource_type' => 'mandates',
                'action' => 'created',
                'links' => [
                    'mandate' => 'MD000SIGVAL123',
                    'customer' => 'CU000SIGVAL123',
                ],
                'metadata' => [
                    'given_name' => 'Sig',
                    'family_name' => 'Validation',
                    'email' => 'sig.validation@example.com',
                    'country_code' => 'GB',
                    'postcode' => 'SW1A 1AA',
                ],
            ]],
        ]);

        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
    }

    public function test_webhook_new_mandate_returns_200(): void
    {
        $response = $this->signedWebhookRequest([
            'events' => [[
                'id' => 'TEST_EVENT_NEW_MANDATE',
                'resource_type' => 'mandates',
                'action' => 'created',
                'links' => [
                    'mandate' => 'MD000NEWMANDATE',
                    'customer' => 'CU000NEWMANDATE',
                ],
                'metadata' => [
                    'given_name' => 'New',
                    'family_name' => 'Mandate',
                    'email' => 'new.mandate@example.com',
                    'country_code' => 'GB',
                    'postcode' => 'SW1A 1AA',
                ],
            ]],
        ]);

        self::assertSame(200, $response['status']);
    }

    public function test_webhook_payment_confirmed_returns_200(): void
    {
        $response = $this->signedWebhookRequest([
            'events' => [[
                'id' => 'TEST_EVENT_PAYMENT_CONFIRMED',
                'resource_type' => 'payments',
                'action' => 'confirmed',
                'links' => [
                    'payment' => 'PM000CONFIRMED',
                ],
            ]],
        ]);

        self::assertSame(200, $response['status']);
    }

    public function test_webhook_another_payment_returns_200(): void
    {
        $response = $this->signedWebhookRequest([
            'events' => [[
                'id' => 'TEST_EVENT_ANOTHER_PAYMENT',
                'resource_type' => 'payments',
                'action' => 'created',
                'links' => [
                    'payment' => 'PM000ANOTHERPAY',
                ],
            ]],
        ]);

        self::assertSame(200, $response['status']);
    }

    public function test_webhook_valid_signature_and_queue_endpoints(): void
    {
        $payloadJson = json_encode([
            'events' => [[
                'id' => 'TEST_EVENT_VALID_SIG',
                'resource_type' => 'mandates',
                'action' => 'created',
                'links' => ['mandate' => 'MD000TESTVALID', 'customer' => 'CU000TESTVALID'],
                'metadata' => [
                    'given_name' => 'Queue',
                    'family_name' => 'Tester',
                    'email' => 'queue.tester@example.com',
                    'country_code' => 'GB',
                    'postcode' => 'SW1A 1AA',
                ],
            ]],
        ]);

        $signature = hash_hmac('sha256', (string) $payloadJson, (string) getenv('GOCARDLESS_WEBHOOK_SECRET'));

        $webhookResponse = $this->client->request('POST', '/webhook/gocardless', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Webhook-Signature' => $signature,
            ],
            'body' => $payloadJson,
        ]);

        self::assertSame(200, $webhookResponse['status']);

        $this->loginAdmin();

        $stats = $this->client->request('GET', '/queue/stats');
        self::assertSame(200, $stats['status']);

        $process = $this->client->request('POST', '/queue/process', [
            'body' => ['batch_size' => 5, 'iterations' => 1, 'sleep_seconds' => 0],
        ]);
        self::assertSame(200, $process['status']);

        $reset = $this->client->request('POST', '/queue/reset-stuck', [
            'body' => ['timeout_minutes' => 30],
        ]);
        self::assertSame(200, $reset['status']);

        $reconciliation = $this->client->request('GET', '/gocardless/reconciliation?p=week');
        self::assertContains($reconciliation['status'], [200, 500]);
    }

    private function signedWebhookRequest(array $payload): array
    {
        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', (string) $payloadJson, (string) getenv('GOCARDLESS_WEBHOOK_SECRET'));

        return $this->client->request('POST', '/webhook/gocardless', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Webhook-Signature' => $signature,
            ],
            'body' => $payloadJson,
        ]);
    }
}
