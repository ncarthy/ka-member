<?php

declare(strict_types=1);

namespace Tests\Integration;

final class NameApiTest extends IntegrationTestCase
{
    public function test_name_crud_flow(): void
    {
        $this->loginAdmin();

        $create = $this->client->request('POST', '/name', [
            'body' => [
                'honorific' => 'Dr',
                'firstname' => 'Unit',
                'surname' => 'Test',
                'idmember' => 8,
            ],
        ]);

        self::assertSame(200, $create['status']);
        $id = (int) ($create['json']['idmembername'] ?? $create['json']['id'] ?? 0);
        self::assertGreaterThan(0, $id);

        $read = $this->client->request('GET', '/name/' . $id);
        self::assertSame(200, $read['status']);
        self::assertSame('Unit', $read['json']['firstname']);

        $update = $this->client->request('PUT', '/name/' . $id, [
            'body' => [
                'honorific' => 'Prof',
                'firstname' => 'UnitUpdated',
                'surname' => 'Test',
            ],
        ]);
        self::assertSame(200, $update['status']);

        $delete = $this->client->request('DELETE', '/name/' . $id);
        self::assertSame(200, $delete['status']);

        $readDeleted = $this->client->request('GET', '/name/' . $id);
        self::assertSame(422, $readDeleted['status']);
    }
}
