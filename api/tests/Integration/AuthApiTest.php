<?php

declare(strict_types=1);

namespace Tests\Integration;

final class AuthApiTest extends IntegrationTestCase
{
    public function test_auth_initial_request_returns_access_token(): void
    {
        $response = $this->client->request('POST', '/auth', [
            'body' => [
                'username' => 'ncarthy',
                'password' => 'treasurer',
            ],
        ]);

        self::assertSame(200, $response['status']);
        self::assertIsArray($response['json']);
        self::assertSame('ncarthy', $response['json']['username']);
        self::assertArrayHasKey('accessToken', $response['json']);
    }

    public function test_auth_invalid_password_returns_401(): void
    {
        $response = $this->client->request('POST', '/auth', [
            'body' => [
                'username' => 'ncarthy',
                'password' => 'wrong-password',
            ],
        ]);

        self::assertSame(401, $response['status']);
        self::assertStringContainsString('Unable to validate', (string) ($response['json']['message'] ?? ''));
    }

    public function test_refresh_and_revoke_flow(): void
    {
        $this->loginAdmin();

        $refresh = $this->client->request('GET', '/auth/refresh');
        self::assertSame(200, $refresh['status']);

        $revoke = $this->client->request('DELETE', '/auth/revoke');
        self::assertSame(200, $revoke['status']);
        $message = strtolower((string) ($revoke['json']['message'] ?? ''));
        self::assertTrue(
            str_contains($message, 'logged out') || str_contains($message, 'not logged in'),
            'Unexpected revoke message: ' . $message
        );
    }

    public function test_protected_route_without_auth_returns_401(): void
    {
        $response = $this->client->request('GET', '/status');
        self::assertSame(401, $response['status']);
    }
}
