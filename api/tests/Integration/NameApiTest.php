<?php

declare(strict_types=1);

namespace Tests\Integration;

final class NameApiTest extends IntegrationTestCase
{
    public function test_name_single_crud_flow(): void
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

    public function test_name_read_routes_and_negative_cases(): void
    {
        $this->loginAdmin();

        $readSeededById = $this->client->request('GET', '/name/6475');
        self::assertSame(200, $readSeededById['status']);
        self::assertSame(8, (int) ($readSeededById['json']['idmember'] ?? 0));

        $readSeededByMember = $this->client->request('GET', '/name/idmember/119');
        self::assertSame(200, $readSeededByMember['status']);
        self::assertIsArray($readSeededByMember['json']);

        $missingRoute = $this->client->request('GET', '/name');
        self::assertSame(404, $missingRoute['status']);

        $invalidId = $this->client->request('GET', '/name/100');
        self::assertSame(422, $invalidId['status']);

        $missingMemberNames = $this->client->request('GET', '/name/idmember/100000');
        self::assertSame(200, $missingMemberNames['status']);
        self::assertIsArray($missingMemberNames['json']);
        self::assertSame(0, count($missingMemberNames['json']));
    }

    public function test_name_bulk_update_and_delete_by_member_flow(): void
    {
        $this->loginAdmin();

        $updateByMember = $this->client->request('PUT', '/name/idmember/119', [
            'body' => [
                [
                    'honorific' => 'Dr',
                    'firstname' => 'Updated',
                    'surname' => 'Alpha',
                ],
                [
                    'honorific' => 'Ms',
                    'firstname' => 'Updated',
                    'surname' => 'Beta',
                ],
            ],
        ]);
        self::assertSame(200, $updateByMember['status']);
        self::assertStringContainsString(
            'updated',
            strtolower((string) ($updateByMember['json']['message'] ?? ''))
        );

        $readUpdatedByMember = $this->client->request('GET', '/name/idmember/119');
        self::assertSame(200, $readUpdatedByMember['status']);
        self::assertIsArray($readUpdatedByMember['json']);
        self::assertSame(2, count($readUpdatedByMember['json']));
        self::assertSame('Alpha', (string) ($readUpdatedByMember['json'][0]['surname'] ?? ''));
        self::assertSame('Beta', (string) ($readUpdatedByMember['json'][1]['surname'] ?? ''));

        $deleteAllByMember = $this->client->request('DELETE', '/name/idmember/119');
        self::assertSame(200, $deleteAllByMember['status']);
        self::assertStringContainsString(
            'removed',
            strtolower((string) ($deleteAllByMember['json']['message'] ?? ''))
        );

        $readAfterDeleteAll = $this->client->request('GET', '/name/idmember/119');
        self::assertSame(200, $readAfterDeleteAll['status']);
        self::assertIsArray($readAfterDeleteAll['json']);
        self::assertSame(0, count($readAfterDeleteAll['json']));
    }
}
