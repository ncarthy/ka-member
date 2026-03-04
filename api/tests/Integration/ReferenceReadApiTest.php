<?php

declare(strict_types=1);

namespace Tests\Integration;

final class ReferenceReadApiTest extends IntegrationTestCase
{
    public function test_reference_read_endpoints(): void
    {
        $this->loginAdmin();

        $cases = [
            '/bank_account',
            '/bank_account/3',
            '/country',
            '/country/186',
            '/country/Uni',
            '/payment_type',
            '/payment_type/3',
            '/status',
            '/status/2',
            '/transactions/summary?start=2021-01-01&end=2021-03-31',
            '/transactions/detail?month=01&year=2021&bankID=3',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertSame(200, $response['status'], 'Expected 200 for ' . $path);
            self::assertIsArray($response['json'], 'Expected JSON response for ' . $path);
        }
    }

    public function test_member_and_name_read_endpoints(): void
    {
        $this->loginAdmin();

        $memberAll = $this->client->request('GET', '/member');
        self::assertSame(200, $memberAll['status']);

        $memberOne = $this->client->request('GET', '/member/8');
        self::assertSame(200, $memberOne['status']);

        $nameByMember = $this->client->request('GET', '/name/idmember/8');
        self::assertSame(200, $nameByMember['status']);
    }
}
