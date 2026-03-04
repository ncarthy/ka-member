<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\ApiServer;
use Tests\Support\HttpClient;
use Tests\Support\TestDatabase;

abstract class IntegrationTestCase extends TestCase
{
    protected static TestDatabase $testDb;
    protected static ApiServer $server;
    protected static bool $bootstrapFailed = false;
    protected static string $bootstrapError = '';
    protected HttpClient $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        try {
            self::$testDb = new TestDatabase();
            self::$testDb->bootEphemeralSchema();

            self::$server = new ApiServer();
            self::$server->start();
        } catch (\Throwable $e) {
            self::$bootstrapFailed = true;
            self::$bootstrapError = $e->getMessage();
            fwrite(STDERR, "[Integration bootstrap] " . self::$bootstrapError . PHP_EOL);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (isset(self::$server)) {
            self::$server->stop();
        }

        if (isset(self::$testDb)) {
            self::$testDb->dropSchema();
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$bootstrapFailed) {
            $this->markTestSkipped('Integration tests skipped: ' . self::$bootstrapError);
        }

        self::$testDb->resetMutableData();
        $this->client = new HttpClient(self::$server->baseUrl());
    }

    protected function loginAdmin(): array
    {
        $response = $this->client->request('POST', '/auth', [
            'body' => [
                'username' => 'ncarthy',
                'password' => 'treasurer',
            ],
        ]);

        self::assertSame(
            200,
            $response['status'],
            'Expected admin login to succeed; body=' . (string) $response['body']
        );
        self::assertIsArray($response['json']);
        self::assertArrayHasKey('accessToken', $response['json']);

        $this->client->setBearerToken($response['json']['accessToken']);
        return $response['json'];
    }

    protected function loginTestUser(): array
    {
        $response = $this->client->request('POST', '/auth', [
            'body' => [
                'username' => 'testuser',
                'password' => 'treasurer',
            ],
        ]);

        self::assertSame(
            200,
            $response['status'],
            'Expected test-user login to succeed; body=' . (string) $response['body']
        );
        self::assertIsArray($response['json']);
        self::assertArrayHasKey('accessToken', $response['json']);

        $this->client->setBearerToken($response['json']['accessToken']);
        return $response['json'];
    }
}
