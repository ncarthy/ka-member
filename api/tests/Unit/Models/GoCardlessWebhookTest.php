<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../core/config.php';
require_once __DIR__ . '/../../../webhook_handlers/abstract_webhook_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/mandate_created_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/payment_created_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/subscription_created_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/subscription_terminated_handler.php';
require_once __DIR__ . '/../../../models/gocardless_webhook.php';

final class GoCardlessWebhookTest extends TestCase
{
    public function test_get_handler_for_supported_events(): void
    {
        $ref = new \ReflectionClass(\Models\GoCardlessWebhook::class);
        $instance = $ref->newInstanceWithoutConstructor();

        $handlerMandate = $instance->getHandlerForEvent('mandates', 'created');
        self::assertInstanceOf(\WebhookHandlers\MandateCreatedHandler::class, $handlerMandate);

        $handlerPayment = $instance->getHandlerForEvent('payments', 'confirmed');
        self::assertInstanceOf(\WebhookHandlers\PaymentCreatedHandler::class, $handlerPayment);

        $handlerSubCreated = $instance->getHandlerForEvent('subscriptions', 'created');
        self::assertInstanceOf(\WebhookHandlers\SubscriptionCreatedHandler::class, $handlerSubCreated);

        $handlerSubTerminated = $instance->getHandlerForEvent('subscriptions', 'cancelled');
        self::assertInstanceOf(\WebhookHandlers\SubscriptionTerminatedHandler::class, $handlerSubTerminated);
    }

    public function test_get_handler_for_unknown_event_returns_null(): void
    {
        $ref = new \ReflectionClass(\Models\GoCardlessWebhook::class);
        $instance = $ref->newInstanceWithoutConstructor();

        self::assertNull($instance->getHandlerForEvent('unknown', 'created'));
    }
}
