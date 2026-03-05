<?php

declare(strict_types=1);

namespace Tests\Integration;

final class MembersApiTest extends IntegrationTestCase
{
    public function test_members_report_endpoints_return_expected_envelopes(): void
    {
        $this->loginAdmin();

        $cases = [
            '/members/life_and_hon',
            '/members/lapsed/18',
            '/members/lapsed/24',
            '/members/summary',
            '/members/cem',
            '/members/discount',
            '/members/payinghonlife',
            '/members/duplicatepayers',
            '/members/noukaddress',
            '/members/mailinglist',
            '/members/emaillist',
            '/members/noemaillist',
            '/members/maplist',
            '/members/invalidemails',
            '/members/invalidpostcodes',
            '/members/lapsedcem/18',
            '/members/lapsedcem/60',
            '/members/formermember/18',
            '/members/oldformermember/36',
            '/members/deletedbutnotformer',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertSame(200, $response['status'], 'Expected 200 for ' . $path . '; body=' . (string) $response['body']);
            $json = $response['json'];
            if (!is_array($json)) {
                $json = $this->decodeJsonFromNoisyBody((string) $response['body']);
            }
            self::assertIsArray($json, 'Expected JSON object for ' . $path . '; body=' . (string) $response['body']);
            self::assertArrayHasKey('count', $json, 'Missing count for ' . $path);
            self::assertArrayHasKey('records', $json, 'Missing records for ' . $path);
            self::assertIsArray($json['records'], 'records should be an array for ' . $path);

            if ($path === '/members/summary') {
                self::assertArrayHasKey('total', $json);
                self::assertIsNumeric($json['total']);
                self::assertGreaterThan(0, (int) $json['count']);
                self::assertGreaterThan(0, count($json['records']));
            } else {
                self::assertSame((int) $json['count'], count($json['records']), 'count mismatch for ' . $path);
            }

            if (in_array($path, [
                '/members/cem',
                '/members/discount',
                '/members/payinghonlife',
                '/members/duplicatepayers',
            ], true)) {
                self::assertArrayHasKey('start', $json, 'Missing start for ' . $path);
                self::assertArrayHasKey('end', $json, 'Missing end for ' . $path);
            }

            if ($path === '/members/lapsed/18' && (int) $json['count'] > 0) {
                self::assertArrayHasKey('membershiptypeid', $json['records'][0]);
            }
        }
    }

    public function test_members_invalid_report_name_returns_404(): void
    {
        $this->loginAdmin();

        $response = $this->client->request('GET', '/members/blahblah');
        self::assertSame(404, $response['status']);
    }

    public function test_members_patch_reports_return_message_and_count(): void
    {
        $this->loginAdmin();

        $lapsedCemPatch = $this->client->request('PATCH', '/members/lapsedcem/10', [
            'body' => ['method' => 'SetToFormer'],
        ]);
        self::assertSame(200, $lapsedCemPatch['status']);
        self::assertIsArray($lapsedCemPatch['json']);
        self::assertArrayHasKey('message', $lapsedCemPatch['json']);
        self::assertArrayHasKey('count', $lapsedCemPatch['json']);

        $oldFormerPatch = $this->client->request('PATCH', '/members/oldformermember/60', [
            'body' => ['method' => 'Anonymize'],
        ]);
        self::assertSame(200, $oldFormerPatch['status']);
        self::assertIsArray($oldFormerPatch['json']);
        self::assertArrayHasKey('count', $oldFormerPatch['json']);
    }

    private function decodeJsonFromNoisyBody(string $body): ?array
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return null;
        }

        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');
        if ($start === false || $end === false || $end < $start) {
            return null;
        }

        $candidate = substr($trimmed, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);
        return is_array($decoded) ? $decoded : null;
    }
}
