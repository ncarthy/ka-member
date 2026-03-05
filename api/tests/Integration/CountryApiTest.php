<?php

declare(strict_types=1);

namespace Tests\Integration;

final class CountryApiTest extends IntegrationTestCase
{
    public function test_read_all_countries_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/country');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertNotEmpty($response['json']);
        self::assertArrayHasKey('id', $response['json'][0]);
        self::assertArrayHasKey('name', $response['json'][0]);
        self::assertArrayHasKey('code', $response['json'][0]);
    }

    public function test_read_country_by_id_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/country/2');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertSame(2, (int) ($response['json']['id'] ?? 0));
        self::assertSame('Zimbabwe', (string) ($response['json']['name'] ?? ''));
        self::assertSame('ZW', (string) ($response['json']['code'] ?? ''));
    }

    public function test_read_country_by_name_prefix_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/country/Zimb');
        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertSame(2, (int) ($response['json']['id'] ?? 0));
        self::assertSame('Zimbabwe', (string) ($response['json']['name'] ?? ''));
    }
}
