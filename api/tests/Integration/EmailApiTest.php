<?php

declare(strict_types=1);

namespace Tests\Integration;

final class EmailApiTest extends IntegrationTestCase
{
    public function test_prepare_reminder_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('POST', '/email/prepare_reminder', [
            'body' => $this->emailPayload(),
        ]);

        self::assertSame(200, $response['status'], 'Body=' . (string) $response['body']);
    }

    public function test_prepare_switch_request_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('POST', '/email/prepare_switchrequest', [
            'body' => $this->emailPayload(),
        ]);

        self::assertSame(200, $response['status'], 'Body=' . (string) $response['body']);
    }

    public function test_send_reminder_returns_200(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('POST', '/email/send_reminder', [
            'body' => $this->emailPayload(),
        ]);

        self::assertSame(200, $response['status'], 'Body=' . (string) $response['body']);
    }

    private function emailPayload(): array
    {
        return [
            'idmember' => 8,
            'fromName' => 'Olivia Cox',
            'fromTitle' => 'Treasurer',
            'fromEmail' => 'membership@knightsbridgeassociation.com',
            'salutation' => 'Dear Mr Teasdale,',
            'toEmail' => 'neil.carthy@fourpointeight.net',
            'subject' => 'Membership Renewal',
        ];
    }
}
