<?php

declare(strict_types=1);

namespace Tests\Integration;

final class FilterAndMembersApiTest extends IntegrationTestCase
{
    public function test_filter_endpoint_variants(): void
    {
        $this->loginAdmin();

        $cases = [
            '/members/filter',
            '/members/filter?surname=Seed',
            '/members/filter?hasemail=y',
            '/members/filter?membershiptype=2',
            '/members/filter?bankaccount=3',
            '/members/filter?paymenttype=3',
            '/members/filter?notdeleted=y',
            '/members/filter?updatedate_start=2020-01-01&updatedate_end=2030-01-01',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertSame(200, $response['status'], 'Expected 200 for ' . $path);
            self::assertIsArray($response['json'], 'Expected JSON for ' . $path);
        }
    }

    public function test_members_report_endpoints(): void
    {
        $this->loginAdmin();

        $cases = [
            '/members/life_and_hon',
            '/members/summary',
            '/members/lapsed/24',
            '/members/cem',
            '/members/discount',
            '/members/payinghonlife',
            '/members/duplicatepayers',
            '/members/invalidemails',
            '/members/invalidpostcodes',
            '/members/deletedbutnotformer',
            '/members/formermember/12',
            '/members/oldformermember/18',
            '/members/maplist',
            '/members/noukaddress',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertContains($response['status'], [200, 404], 'Unexpected status for ' . $path);
        }
    }
}
