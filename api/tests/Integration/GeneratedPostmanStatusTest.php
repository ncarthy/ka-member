<?php

declare(strict_types=1);

namespace Tests\Integration;

final class GeneratedPostmanStatusTest extends IntegrationTestCase
{
    /**
     * @dataProvider postmanStatusCases
     */
    public function test_postman_status_case(string $name, string $method, string $path, int $expectedStatus): void
    {
        if (getenv('RUN_GENERATED_POSTMAN') !== '1') {
            self::markTestSkipped('Generated Postman matrix is opt-in. Set RUN_GENERATED_POSTMAN=1 to execute.');
        }

        if (!str_starts_with($path, '/auth') && !str_starts_with($path, '/webhook')) {
            $this->loginAdmin();
        }

        $response = $this->client->request($method, $path);
        self::assertSame($expectedStatus, $response['status'], $name . " failed for " . $path);
    }

    public static function postmanStatusCases(): array
    {
        return [
            'gocardless/Another Payment' => ['gocardless/Another Payment', 'POST', '/webhook/gocardless', 200],
            'gocardless/Invalid Signature' => ['gocardless/Invalid Signature', 'POST', '/webhook/gocardless', 401],
            'gocardless/Missing Signature' => ['gocardless/Missing Signature', 'POST', '/webhook/gocardless', 400],
            'gocardless/New Mandate' => ['gocardless/New Mandate', 'POST', '/webhook/gocardless', 200],
            'gocardless/Payment Confirmed' => ['gocardless/Payment Confirmed', 'POST', '/webhook/gocardless', 200],
            'gocardless/Signature Validation' => ['gocardless/Signature Validation', 'POST', '/webhook/gocardless', 200],
            'gocardless/Test Route' => ['gocardless/Test Route', 'POST', '/webhook/gocardless', 400],
        ];
    }
}
