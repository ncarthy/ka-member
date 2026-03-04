<?php

declare(strict_types=1);

namespace Tests\Integration;

final class TransactionApiTest extends IntegrationTestCase
{
    public function test_transaction_crud_flow(): void
    {
        $this->loginAdmin();

        $create = $this->client->request('POST', '/transaction', [
            'body' => [
                'date' => '2025-01-01',
                'amount' => '99.99',
                'paymenttypeID' => 3,
                'idmember' => 8,
                'bankID' => 3,
                'note' => 'PHPUnit transaction',
            ],
        ]);

        self::assertSame(200, $create['status']);
        $id = (int) ($create['json']['id'] ?? 0);
        self::assertGreaterThan(0, $id);

        $read = $this->client->request('GET', '/transaction/' . $id);
        self::assertSame(200, $read['status']);

        $update = $this->client->request('PUT', '/transaction/' . $id, [
            'body' => [
                'date' => '2025-01-02',
                'amount' => '50.00',
                'paymenttypeID' => 2,
                'idmember' => 8,
                'bankID' => 1,
                'note' => 'Updated PHPUnit transaction',
            ],
        ]);
        self::assertSame(200, $update['status']);

        $delete = $this->client->request('DELETE', '/transaction/' . $id);
        self::assertSame(200, $delete['status']);

        $readDeleted = $this->client->request('GET', '/transaction/' . $id);
        self::assertSame(422, $readDeleted['status']);
    }
}
