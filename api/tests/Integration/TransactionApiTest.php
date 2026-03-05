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

    public function test_transaction_read_routes(): void
    {
        $this->loginAdmin();

        $readAll = $this->client->request('GET', '/transaction');
        self::assertSame(200, $readAll['status']);
        self::assertIsArray($readAll['json']);
        self::assertGreaterThan(0, count($readAll['json']));

        $readOne = $this->client->request('GET', '/transaction/3150');
        self::assertSame(200, $readOne['status']);
        self::assertSame(3150, (int) ($readOne['json']['id'] ?? 0));

        $readInvalid = $this->client->request('GET', '/transaction/1');
        self::assertSame(422, $readInvalid['status']);
        self::assertStringContainsString('no transaction found', strtolower((string) ($readInvalid['json']['message'] ?? '')));

        $readByMember = $this->client->request('GET', '/transaction/idmember/8');
        self::assertSame(200, $readByMember['status']);
        self::assertIsArray($readByMember['json']);
    }

    public function test_transaction_delete_by_member_flow(): void
    {
        $this->loginAdmin();
        $memberId = $this->createMemberForTransactionTests();

        $create1 = $this->client->request('POST', '/transaction', [
            'body' => $this->transactionPayload($memberId, '2025-01-10', '10.00', 1, 1, 'tx 1'),
        ]);
        self::assertSame(200, $create1['status']);

        $create2 = $this->client->request('POST', '/transaction', [
            'body' => $this->transactionPayload($memberId, '2025-01-11', '20.00', 2, 3, 'tx 2'),
        ]);
        self::assertSame(200, $create2['status']);

        $readBeforeDelete = $this->client->request('GET', '/transaction/idmember/' . $memberId);
        self::assertSame(200, $readBeforeDelete['status']);
        self::assertIsArray($readBeforeDelete['json']);
        self::assertGreaterThanOrEqual(2, count($readBeforeDelete['json']));

        $deleteByMember = $this->client->request('DELETE', '/transaction/idmember/' . $memberId);
        self::assertSame(200, $deleteByMember['status']);

        $readAfterDelete = $this->client->request('GET', '/transaction/idmember/' . $memberId);
        self::assertSame(200, $readAfterDelete['status']);
        self::assertIsArray($readAfterDelete['json']);
        self::assertSame(0, count($readAfterDelete['json']));
    }

    public function test_transactions_report_routes(): void
    {
        $this->loginAdmin();

        $detail = $this->client->request('GET', '/transactions/detail?month=01&year=2021&bankID=3');
        self::assertSame(200, $detail['status']);
        self::assertIsArray($detail['json']);
        self::assertArrayHasKey('records', $detail['json']);
        self::assertArrayHasKey('count', $detail['json']);

        $summary = $this->client->request('GET', '/transactions/summary?start=2021-01-01&end=2021-03-31');
        self::assertSame(200, $summary['status']);
        self::assertIsArray($summary['json']);
        self::assertArrayHasKey('records', $summary['json']);
        self::assertArrayHasKey('count', $summary['json']);
        self::assertArrayHasKey('total', $summary['json']);

        $summaryWithOpenEnd = $this->client->request('GET', '/transactions/summary?start=2021-01-01&end=&bankID=3');
        self::assertSame(200, $summaryWithOpenEnd['status']);
        self::assertIsArray($summaryWithOpenEnd['json']);
        self::assertArrayHasKey('records', $summaryWithOpenEnd['json']);
        self::assertArrayHasKey('count', $summaryWithOpenEnd['json']);
        self::assertArrayHasKey('total', $summaryWithOpenEnd['json']);
    }

    private function transactionPayload(
        int $memberId,
        string $date,
        string $amount,
        int $paymentTypeId,
        int $bankId,
        string $note
    ): array {
        return [
            'date' => $date,
            'amount' => $amount,
            'paymenttypeID' => $paymentTypeId,
            'idmember' => $memberId,
            'bankID' => $bankId,
            'note' => $note,
        ];
    }

    private function createMemberForTransactionTests(): int
    {
        $createMember = $this->client->request('POST', '/member', [
            'body' => [
                'title' => '',
                'businessname' => 'Txn Test Member',
                'bankpayerref' => '',
                'note' => 'Transaction test member',
                'addressfirstline' => '99 Test Road',
                'addresssecondline' => '',
                'city' => 'London',
                'county' => '',
                'postcode' => 'SW1A 1AA',
                'countryID' => 186,
                'area' => '',
                'email1' => 'txn.member@example.com',
                'phone1' => '02071111111',
                'addressfirstline2' => '',
                'addresssecondline2' => '',
                'city2' => '',
                'county2' => '',
                'postcode2' => '',
                'country2ID' => null,
                'email2' => '',
                'phone2' => '',
                'statusID' => 2,
                'expirydate' => '2030-01-01',
                'joindate' => null,
                'reminderdate' => null,
                'deletedate' => null,
                'repeatpayment' => 0,
                'recurringpayment' => 0,
                'gdpr_email' => true,
                'gdpr_tel' => true,
                'gdpr_address' => true,
                'gdpr_sm' => false,
                'postonhold' => false,
                'emailonhold' => false,
            ],
        ]);

        self::assertSame(200, $createMember['status'], 'Expected member creation to succeed for transaction tests');
        $memberId = (int) ($createMember['json']['id'] ?? 0);
        self::assertGreaterThan(0, $memberId);

        return $memberId;
    }
}
