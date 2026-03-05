<?php

declare(strict_types=1);

namespace Tests\Integration;

final class BankAccountApiTest extends IntegrationTestCase
{
    public function test_read_all_bank_accounts_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/bank_account');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertNotEmpty($response['json']);
        self::assertArrayHasKey('id', $response['json'][0]);
        self::assertArrayHasKey('name', $response['json'][0]);
    }

    public function test_read_one_bank_account_by_id_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/bank_account/2');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertSame(2, (int) ($response['json']['id'] ?? 0));
        self::assertNotSame('', (string) ($response['json']['name'] ?? ''));
    }

    public function test_read_one_bank_account_by_name_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/bank_account/paypal');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertSame(3, (int) ($response['json']['id'] ?? 0));
        self::assertStringContainsStringIgnoringCase('paypal', (string) ($response['json']['name'] ?? ''));
    }
}
