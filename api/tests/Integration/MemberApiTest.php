<?php

declare(strict_types=1);

namespace Tests\Integration;

final class MemberApiTest extends IntegrationTestCase
{
    public function test_member_crud_and_patch_flow(): void
    {
        $this->loginAdmin();

        $create = $this->client->request('POST', '/member', [
            'body' => [
                'title' => '',
                'businessname' => 'PHPUnit Member Co',
                'bankpayerref' => '',
                'note' => 'Created by PHPUnit',
                'addressfirstline' => '10 Test Lane',
                'addresssecondline' => '',
                'city' => 'London',
                'county' => '',
                'postcode' => 'SW1A 1AA',
                'countryID' => 186,
                'area' => '',
                'email1' => 'member.phpunit@example.com',
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
            ],
        ]);

        self::assertSame(200, $create['status']);
        $id = (int) ($create['json']['id'] ?? 0);
        self::assertGreaterThan(0, $id);

        $read = $this->client->request('GET', '/member/' . $id);
        self::assertSame(200, $read['status']);
        self::assertSame('PHPUnit Member Co', $read['json']['businessname']);

        $update = $this->client->request('PUT', '/member/' . $id, [
            'body' => [
                'title' => '',
                'businessname' => 'PHPUnit Member Co Updated',
                'bankpayerref' => 'BPR001',
                'note' => 'Updated by PHPUnit',
                'addressfirstline' => '10 Test Lane',
                'addresssecondline' => '',
                'city' => 'London',
                'county' => '',
                'postcode' => 'SW1A 1AA',
                'countryID' => 186,
                'area' => '',
                'email1' => 'member.updated@example.com',
                'phone1' => '02079999999',
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
                'gdpr_email' => false,
                'gdpr_tel' => false,
                'gdpr_address' => false,
                'gdpr_sm' => false,
                'postonhold' => false,
                'emailonhold' => false,
                'gpslat1' => 51.5,
                'gpslng1' => -0.12,
            ],
        ]);
        self::assertSame(200, $update['status']);

        $patch = $this->client->request('PATCH', '/member/' . $id, [
            'body' => ['method' => 'setReminderDate'],
        ]);
        self::assertSame(200, $patch['status']);

        $delete = $this->client->request('DELETE', '/member/' . $id);
        self::assertSame(200, $delete['status']);
    }
}
