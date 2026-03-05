<?php

declare(strict_types=1);

namespace Tests\Integration;

final class MemberApiTest extends IntegrationTestCase
{
    public function test_member_lifecycle_create_update_patch_delete(): void
    {
        $this->loginAdmin();

        $createPayload = $this->buildMemberPayload('PHPUnit Member Co', 'member.phpunit@example.com');
        $create = $this->client->request('POST', '/member', [
            'body' => $createPayload,
        ]);

        self::assertSame(200, $create['status']);
        self::assertIsArray($create['json']);
        $id = (int) ($create['json']['id'] ?? 0);
        self::assertGreaterThan(0, $id);
        self::assertStringContainsString('created', strtolower((string) ($create['json']['message'] ?? '')));

        $createdRead = $this->client->request('GET', '/member/' . $id);
        self::assertSame(200, $createdRead['status']);
        self::assertIsArray($createdRead['json']);
        self::assertSame('PHPUnit Member Co', (string) ($createdRead['json']['businessname'] ?? ''));
        self::assertSame('member.phpunit@example.com', (string) ($createdRead['json']['email1'] ?? ''));
        self::assertSame('Created by PHPUnit', (string) ($createdRead['json']['note'] ?? ''));
        self::assertSame(186, (int) ($createdRead['json']['primaryAddress']['country'] ?? 0));
        self::assertSame('10 Test Lane', (string) ($createdRead['json']['primaryAddress']['addressfirstline'] ?? ''));

        $updatePayload = $this->buildMemberPayload('PHPUnit Member Co Updated', 'member.updated@example.com');
        $updatePayload['bankpayerref'] = 'BPR001';
        $updatePayload['note'] = 'Updated by PHPUnit';
        $updatePayload['gdpr_email'] = false;
        $updatePayload['gdpr_tel'] = false;
        $updatePayload['gdpr_address'] = false;
        $updatePayload['postonhold'] = true;
        $updatePayload['emailonhold'] = true;
        $update = $this->client->request('PUT', '/member/' . $id, [
            'body' => $updatePayload,
        ]);
        self::assertSame(200, $update['status']);
        self::assertStringContainsString('updated', strtolower((string) ($update['json']['message'] ?? '')));

        $updatedRead = $this->client->request('GET', '/member/' . $id);
        self::assertSame(200, $updatedRead['status']);
        self::assertSame('PHPUnit Member Co Updated', (string) ($updatedRead['json']['businessname'] ?? ''));
        self::assertSame('member.updated@example.com', (string) ($updatedRead['json']['email1'] ?? ''));
        self::assertSame('Updated by PHPUnit', (string) ($updatedRead['json']['note'] ?? ''));
        self::assertSame('BPR001', (string) ($updatedRead['json']['bankpayerref'] ?? ''));
        self::assertFalse((bool) ($updatedRead['json']['gdpr_email'] ?? true));
        self::assertFalse((bool) ($updatedRead['json']['gdpr_tel'] ?? true));
        self::assertFalse((bool) ($updatedRead['json']['gdpr_address'] ?? true));
        self::assertTrue((bool) ($updatedRead['json']['postonhold'] ?? false));
        self::assertTrue((bool) ($updatedRead['json']['emailonhold'] ?? false));

        $patch = $this->client->request('PATCH', '/member/' . $id, [
            'body' => ['method' => 'setReminderDate'],
        ]);
        self::assertSame(200, $patch['status']);
        self::assertStringContainsString('success', strtolower((string) ($patch['json']['message'] ?? '')));
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) ($patch['json']['reminderdate'] ?? ''));

        $geometryPatch = $this->client->request('PATCH', '/member/' . $id, [
            'body' => [
                'method' => 'setprimarygeometry',
                'gpslat' => 51.50101,
                'gpslng' => -0.14159,
            ],
        ]);
        self::assertSame(200, $geometryPatch['status']);

        $afterPatchRead = $this->client->request('GET', '/member/' . $id);
        self::assertSame(200, $afterPatchRead['status']);
        self::assertNotSame('', (string) ($afterPatchRead['json']['reminderdate'] ?? ''));
        self::assertSame(51.50101, (float) ($afterPatchRead['json']['primaryAddress']['lat'] ?? 0.0));
        self::assertSame(-0.14159, (float) ($afterPatchRead['json']['primaryAddress']['lng'] ?? 0.0));

        $delete = $this->client->request('DELETE', '/member/' . $id);
        self::assertSame(200, $delete['status']);
        self::assertStringContainsString('deleted', strtolower((string) ($delete['json']['message'] ?? '')));

        $readDeleted = $this->client->request('GET', '/member/' . $id);
        self::assertSame(422, $readDeleted['status']);
        self::assertStringContainsString('no member found', strtolower((string) ($readDeleted['json']['message'] ?? '')));
    }

    public function test_member_read_all_and_seeded_member_read(): void
    {
        $this->loginAdmin();

        $all = $this->client->request('GET', '/member');
        self::assertSame(200, $all['status']);
        self::assertIsArray($all['json']);
        self::assertGreaterThan(0, count($all['json']));

        $one = $this->client->request('GET', '/member/278');
        self::assertSame(200, $one['status']);
        self::assertIsArray($one['json']);
        self::assertSame(278, (int) ($one['json']['id'] ?? 0));
        self::assertArrayHasKey('primaryAddress', $one['json']);
    }

    private function buildMemberPayload(string $businessName, string $email1): array
    {
        return [
            'title' => '',
            'businessname' => $businessName,
            'bankpayerref' => '',
            'note' => 'Created by PHPUnit',
            'addressfirstline' => '10 Test Lane',
            'addresssecondline' => '',
            'city' => 'London',
            'county' => '',
            'postcode' => 'SW1A 1AA',
            'countryID' => 186,
            'area' => '',
            'email1' => $email1,
            'phone1' => '02070000000',
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
            'gpslat1' => null,
            'gpslng1' => null,
        ];
    }
}
