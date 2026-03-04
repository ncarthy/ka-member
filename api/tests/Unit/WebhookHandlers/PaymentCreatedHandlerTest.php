<?php

declare(strict_types=1);

namespace Tests\Unit\WebhookHandlers;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use WebhookHandlers\PaymentCreatedHandler;

require_once __DIR__ . '/../../../core/config.php';
require_once __DIR__ . '/../../../webhook_handlers/abstract_webhook_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/payment_created_handler.php';

final class PaymentCreatedHandlerTest extends TestCase
{
    public function test_handle_throws_when_payment_link_missing(): void
    {
        $handler = new StubPaymentCreatedHandler($this->createMock(PDO::class), new \stdClass(), new \stdClass());
        $event = (object) ['id' => 'EV_1', 'links' => (object) []];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing required payment data');

        $handler->handle($event, $this->fakeWebhookLog());
    }

    public function test_handle_returns_duplicate_status_when_transaction_already_exists(): void
    {
        $memberStmt = $this->createMock(PDOStatement::class);
        $memberStmt->method('bindParam')->willReturn(true);
        $memberStmt->method('execute')->willReturn(true);
        $memberStmt->method('rowCount')->willReturn(1);
        $memberStmt->method('fetch')->willReturn(['member_idmember' => 8]);

        $dupStmt = $this->createMock(PDOStatement::class);
        $dupStmt->method('bindParam')->willReturn(true);
        $dupStmt->method('execute')->willReturn(true);
        $dupStmt->method('rowCount')->willReturn(1);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($memberStmt, $dupStmt);

        $payment = (object) [
            'amount' => 5000,
            'charge_date' => '2026-01-01',
            'links' => (object) ['mandate' => 'MD_TEST_1'],
        ];

        $handler = new StubPaymentCreatedHandler($pdo, new \stdClass(), $payment);
        $event = (object) [
            'id' => 'EV_TEST_1',
            'links' => (object) ['payment' => 'PM_TEST_1'],
        ];

        $webhookLog = $this->fakeWebhookLog();
        $result = $handler->handle($event, $webhookLog);

        self::assertSame('duplicate_transaction', $result['status']);
        self::assertSame('PM_TEST_1', $result['payment_id']);
    }

    public function test_handle_inserts_transaction_for_new_payment(): void
    {
        $memberStmt = $this->createMock(PDOStatement::class);
        $memberStmt->method('bindParam')->willReturn(true);
        $memberStmt->method('execute')->willReturn(true);
        $memberStmt->method('rowCount')->willReturn(1);
        $memberStmt->method('fetch')->willReturn(['member_idmember' => 8]);

        $dupStmt = $this->createMock(PDOStatement::class);
        $dupStmt->method('bindParam')->willReturn(true);
        $dupStmt->method('execute')->willReturn(true);
        $dupStmt->method('rowCount')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->method('bindParam')->willReturn(true);
        $insertStmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($memberStmt, $dupStmt, $insertStmt);
        $pdo->method('lastInsertId')->willReturn('1234');

        $payment = (object) [
            'amount' => 5000,
            'charge_date' => '2026-01-01',
            'links' => (object) ['mandate' => 'MD_TEST_1'],
        ];

        $handler = new StubPaymentCreatedHandler($pdo, new \stdClass(), $payment);
        $event = (object) [
            'id' => 'EV_TEST_2',
            'links' => (object) ['payment' => 'PM_TEST_2'],
        ];

        $result = $handler->handle($event, $this->fakeWebhookLog());

        self::assertSame('success', $result['status']);
        self::assertSame('1234', (string) $result['transaction_id']);
        self::assertSame(50.0, (float) $result['amount']);
    }

    private function fakeWebhookLog(): object
    {
        return new class {
            public function markProcessed($id = null): bool
            {
                return true;
            }
        };
    }
}

final class StubPaymentCreatedHandler extends PaymentCreatedHandler
{
    private object $payment;

    public function __construct($conn, $client, object $payment)
    {
        parent::__construct($conn, $client);
        $this->payment = $payment;
    }

    protected function getPaymentDetails($payment_id)
    {
        return $this->payment;
    }
}
