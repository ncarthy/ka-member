<?php

declare(strict_types=1);

namespace Tests\Integration;

final class PaymentTypeApiTest extends IntegrationTestCase
{
    public function test_payment_type_read_routes_return_200(): void
    {
        $this->loginAdmin();

        $cases = [
            '/payment_type',
            '/payment_type/2',
            '/payment_type/cash',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertSame(200, $response['status'], 'Expected 200 for ' . $path . '; body=' . (string) $response['body']);
        }
    }
}
