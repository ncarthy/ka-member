<?php

declare(strict_types=1);

namespace Tests\Integration;

final class UserApiTest extends IntegrationTestCase
{
    public function test_admin_can_create_read_update_delete_user(): void
    {
        $this->loginAdmin();

        $allBefore = $this->client->request('GET', '/user');
        self::assertSame(200, $allBefore['status']);
        self::assertIsArray($allBefore['json']);

        $create = $this->client->request('POST', '/user', [
            'body' => [
                'username' => 'phpunit_user',
                'fullname' => 'PHPUnit User',
                'role' => 'User',
                'email' => 'phpunit.user@example.com',
                'title' => 'Mx',
                'suspended' => false,
                'password' => 'Passw0rd!',
            ],
        ]);

        self::assertSame(200, $create['status']);
        $id = (int) ($create['json']['id'] ?? 0);
        self::assertGreaterThan(0, $id);

        $allAfterCreate = $this->client->request('GET', '/user');
        self::assertSame(200, $allAfterCreate['status']);
        self::assertIsArray($allAfterCreate['json']);
        self::assertGreaterThanOrEqual(count($allBefore['json']), count($allAfterCreate['json']));

        $read = $this->client->request('GET', '/user/' . $id);
        self::assertSame(200, $read['status']);
        self::assertSame('phpunit_user', $read['json']['username']);

        $update = $this->client->request('PUT', '/user/' . $id, [
            'body' => [
                'username' => 'phpunit_user',
                'fullname' => 'Updated User',
                'role' => 'Admin',
                'email' => 'updated.user@example.com',
                'title' => 'Dr',
                'suspended' => false,
                'failedloginattempts' => 0,
                'password' => 'N3wPassw0rd!',
            ],
        ]);
        self::assertSame(200, $update['status']);

        $delete = $this->client->request('DELETE', '/user/' . $id);
        self::assertSame(200, $delete['status']);

        $readDeleted = $this->client->request('GET', '/user/' . $id);
        self::assertSame(422, $readDeleted['status']);
    }

    public function test_password_complexity_validation_returns_422(): void
    {
        $this->loginAdmin();

        $weak = $this->client->request('POST', '/user', [
            'body' => [
                'username' => 'weak_user',
                'fullname' => 'Weak User',
                'role' => 'User',
                'email' => 'weak.user@example.com',
                'title' => 'Mx',
                'suspended' => false,
                'password' => 'weak',
            ],
        ]);

        self::assertSame(422, $weak['status']);
        self::assertIsArray($weak['json']);
        self::assertStringContainsString('password', strtolower((string) ($weak['json']['message'] ?? '')));
    }

    public function test_non_admin_user_authorization_rules(): void
    {
        $this->loginTestUser();

        $readAll = $this->client->request('GET', '/user');
        self::assertSame(401, $readAll['status']);

        $otherUser = $this->client->request('GET', '/user/1');
        self::assertSame(401, $otherUser['status']);

        $ownUser = $this->client->request('GET', '/user/20');
        self::assertSame(200, $ownUser['status']);

        $invalidId = $this->client->request('GET', '/user/5000');
        self::assertSame(401, $invalidId['status']);

        $updateOther = $this->client->request('PUT', '/user/1', [
            'body' => [
                'username' => 'ncarthy',
                'fullname' => 'Should Not Update',
                'role' => 'Admin',
                'email' => 'ncarthy@example.com',
                'title' => 'Mr',
                'suspended' => false,
                'failedloginattempts' => 0,
            ],
        ]);
        self::assertSame(401, $updateOther['status']);

        $updateOwn = $this->client->request('PUT', '/user/20', [
            'body' => [
                'username' => 'testuser',
                'fullname' => 'Test User Updated',
                'role' => 'User',
                'email' => 'testuser.updated@example.com',
                'title' => 'Ms',
                'suspended' => false,
                'failedloginattempts' => 0,
            ],
        ]);
        self::assertSame(200, $updateOwn['status']);

        $readOwnAfterUpdate = $this->client->request('GET', '/user/20');
        self::assertSame(200, $readOwnAfterUpdate['status']);
        self::assertSame('testuser', (string) ($readOwnAfterUpdate['json']['username'] ?? ''));
    }

    public function test_admin_read_one_invalid_id_returns_422(): void
    {
        $this->loginAdmin();

        $invalid = $this->client->request('GET', '/user/5000');
        self::assertSame(422, $invalid['status']);
        self::assertIsArray($invalid['json']);
        self::assertStringContainsString('no user found', strtolower((string) ($invalid['json']['message'] ?? '')));
    }
}
