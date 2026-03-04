<?php

declare(strict_types=1);

namespace Tests\Integration;

final class UserApiTest extends IntegrationTestCase
{
    public function test_admin_can_crud_user(): void
    {
        $this->loginAdmin();

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

    public function test_non_admin_cannot_read_other_users(): void
    {
        $this->loginTestUser();

        $otherUser = $this->client->request('GET', '/user/1');
        self::assertSame(401, $otherUser['status']);

        $ownUser = $this->client->request('GET', '/user/20');
        self::assertSame(200, $ownUser['status']);
    }
}
