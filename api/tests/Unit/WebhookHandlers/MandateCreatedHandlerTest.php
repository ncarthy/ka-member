<?php

declare(strict_types=1);

namespace Tests\Unit\WebhookHandlers;

use PHPUnit\Framework\TestCase;
use WebhookHandlers\MandateCreatedHandler;

require_once __DIR__ . '/../../../core/config.php';
require_once __DIR__ . '/../../../webhook_handlers/abstract_webhook_handler.php';
require_once __DIR__ . '/../../../webhook_handlers/mandate_created_handler.php';

final class MandateCreatedHandlerTest extends TestCase
{
    public function test_handle_throws_when_mandate_link_missing(): void
    {
        $handler = new TestableMandateCreatedHandler(new \stdClass(), new \stdClass());
        $event = (object) ['id' => 'EV_X', 'links' => (object) []];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing mandate ID in event');

        $handler->handle($event, new class {
            public function markProcessed($id = null): bool
            {
                return true;
            }
        });
    }

    public function test_extract_customer_data_trims_fields(): void
    {
        $handler = new TestableMandateCreatedHandler(new \stdClass(), new \stdClass());

        $data = $handler->extract((object) [
            'given_name' => ' John ',
            'family_name' => ' Smith ',
            'company_name' => ' ACME Ltd ',
            'email' => ' john@example.com ',
            'country_code' => ' GB ',
            'address_line1' => ' 1 Test St ',
            'postal_code' => ' SW1A 1AA ',
        ]);

        self::assertSame('John', $data['given_name']);
        self::assertSame('Smith', $data['family_name']);
        self::assertSame('john@example.com', $data['email']);
        self::assertSame('SW1A 1AA', $data['postcode']);
    }

    public function test_validate_customer_data_requires_email_and_name(): void
    {
        $handler = new TestableMandateCreatedHandler(new \stdClass(), new \stdClass());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing email in GoCardless customer data');

        $handler->validate([
            'company_name' => 'Company',
            'given_name' => '',
            'family_name' => '',
            'email' => '',
        ]);
    }

    public function test_normalize_helpers_lowercase_and_strip_spaces(): void
    {
        $handler = new TestableMandateCreatedHandler(new \stdClass(), new \stdClass());

        self::assertSame('abc', $handler->normalize(' AbC '));
        self::assertSame('sw1a1aa', $handler->normalizePostcodeValue(' SW1A 1AA '));
    }
}

final class TestableMandateCreatedHandler extends MandateCreatedHandler
{
    public function extract(object $customer): array
    {
        return $this->extractCustomerData($customer);
    }

    public function validate(array $data): void
    {
        $this->validateCustomerData($data);
    }

    public function normalize(string $value): string
    {
        return $this->normalizeText($value);
    }

    public function normalizePostcodeValue(string $value): string
    {
        return $this->normalizePostcode($value);
    }
}
