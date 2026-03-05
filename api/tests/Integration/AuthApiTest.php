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

    public function test_auth_invalid_password_six_attempts_return_401_and_then_suspend(): void
    {
        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $response = $this->client->request('POST', '/auth', [
                'body' => [
                    'username' => 'ncarthy',
                    'password' => 'wrong-password',
                ],
            ]);

            self::assertSame(401, $response['status'], 'Unexpected status on attempt #' . $attempt);

            $message = strtolower((string) ($response['json']['message'] ?? ''));
            if ($attempt < 6) {
                self::assertStringContainsString('unable to validate', $message, 'Unexpected message on attempt #' . $attempt);
            } else {
                self::assertStringContainsString('suspended', $message, 'Expected suspension on attempt #6');
            }
        }
    }

    public function test_invalid_bearer_token_returns_401(): void
    {
        $auth = $this->loginAdmin();
        $accessToken = (string) ($auth['accessToken'] ?? '');
        $parts = explode('.', $accessToken);
        self::assertCount(3, $parts, 'Expected JWT access token with 3 segments.');

        $signature = $parts[2];
        $lastChar = substr($signature, -1);
        $replacement = $lastChar === 'a' ? 'b' : 'a';
        $tamperedSignature = substr($signature, 0, -1) . $replacement;
        $invalidToken = $parts[0] . '.' . $parts[1] . '.' . $tamperedSignature;

        $response = $this->client->request('GET', '/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $invalidToken,
            ],
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_wrong_audience_claim_returns_401(): void
    {
        $auth = $this->loginAdmin();
        $accessToken = (string) ($auth['accessToken'] ?? '');
        $mutatedToken = $this->withJwtPayloadClaim($accessToken, 'aud', 'https://invalid-audience.local');

        $response = $this->client->request('GET', '/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $mutatedToken,
            ],
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_wrong_issuer_claim_returns_401(): void
    {
        $auth = $this->loginAdmin();
        $accessToken = (string) ($auth['accessToken'] ?? '');
        $mutatedToken = $this->withJwtPayloadClaim($accessToken, 'iss', 'https://invalid-issuer.local');

        $response = $this->client->request('GET', '/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $mutatedToken,
            ],
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_prerequest_status_with_valid_bearer_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/status');
        self::assertSame(200, $response['status']);
    }

    public function test_expired_access_token_returns_401(): void
    {
        $auth = $this->loginAdmin();
        $accessToken = (string) ($auth['accessToken'] ?? '');
        $expiredToken = $this->withJwtPayloadClaim($accessToken, 'exp', time() - 3600);

        $response = $this->client->request('GET', '/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $expiredToken,
            ],
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_invalid_jti_claim_returns_401(): void
    {
        $auth = $this->loginAdmin();
        $accessToken = (string) ($auth['accessToken'] ?? '');
        $invalidJtiToken = $this->withJwtPayloadClaim($accessToken, 'jti', 'not-a-valid-db-jti');

        $response = $this->client->request('GET', '/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $invalidJtiToken,
            ],
        ]);

        self::assertSame(401, $response['status']);
    }

    public function test_logout_returns_200(): void
    {
        $this->loginAdmin();
        $revoke = $this->client->request('DELETE', '/auth/revoke');

        self::assertSame(200, $revoke['status']);
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

    private function withJwtPayloadClaim(string $jwt, string $claim, mixed $value): string
    {
        $parts = explode('.', $jwt);
        self::assertCount(3, $parts, 'Expected JWT with 3 segments.');

        $headerJson = self::base64UrlDecode($parts[0]);
        $payloadJson = self::base64UrlDecode($parts[1]);
        self::assertNotFalse($headerJson, 'Unable to decode JWT header');
        self::assertNotFalse($payloadJson, 'Unable to decode JWT payload');

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);
        self::assertIsArray($header);
        self::assertIsArray($payload);

        $payload[$claim] = $value;

        $newHeader = self::base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $newPayload = self::base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signingInput = $newHeader . '.' . $newPayload;

        $key = getenv('KA_MEMBER_KEY');
        self::assertNotFalse($key, 'KA_MEMBER_KEY must be set for JWT mutation tests.');

        $signature = hash_hmac('sha256', $signingInput, (string) $key, true);
        $newSignature = self::base64UrlEncode($signature);

        return $signingInput . '.' . $newSignature;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string|false
    {
        $padded = strtr($value, '-_', '+/');
        $remainder = strlen($padded) % 4;
        if ($remainder !== 0) {
            $padded .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode($padded, true);
    }
}
