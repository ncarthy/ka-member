<?php

declare(strict_types=1);

namespace Tests\Integration;

final class FilterApiTest extends IntegrationTestCase
{
    public function test_filter_routes_match_deep_assertions(): void
    {
        $this->loginAdmin();

        $cases = [
            ['/members/filter?address=september&removed=y', 200],
            ['/members/filter?removed=any', 200],
            ['/members/filter?removed=any&businessorsurname=', 200],
            ['/members/filter?bankaccountid=1', 200],
            ['/members/filter?businessname=bonha', 200],
            ['/members/filter', 200],
            ['/members/filter?surname=cox', 200],
            ['/members/filter?deletedatestart=2020-06-01&deletedateend=2020-08-31', 200],
            ['/members/filter?removed=y', 200],
            ['/members/filter?email=jeffest', 200],
            ['/members/filter?expirydateend=2020-12-31', 200],
            ['/members/filter?expirydatestart=2020-12-31', 200],
            ['/members/filter?hasemail=any', 200],
            ['/members/filter?hasemail=n', 200],
            ['/members/filter?hasemail=y', 200],
            ['/members/filter?expirydateend=2020-12-310', 422],
            ['/members/filter?surname=asfdkjhsakf', 200],
            ['/members/filter?lasttransactiondatestart=2025-12-01&lasttransactiondateend=2025-12-31', 200],
            ['/members/filter?paymentmethod=cash', 200],
            ['/members/filter?paymentmethod=cheq&lasttransactiondateend=2021-02-08', 200],
            ['/members/filter?membertypeid=5', 200],
            ['/members/filter?membertyperange=5,6', 200],
            ['/members/filter?removed=n', 200],
            ['/members/filter?paymenttypeid=5', 200],
            ['/members/filter?reminderdatestart=2020-06-01&reminderdateend=2020-06-30', 200],
            ['/members/filter?surname=bha&removed=any', 200],
            ['/members/filter?businessorsurname=car&removed=any', 200],
            ['/members/filter?updatedatestart=2021-01-01&updatedateend=2021-01-31&removed=any', 200],
        ];

        $cachedResponses = [];
        foreach ($cases as [$path, $expectedStatus]) {
            $response = $this->client->request('GET', $path);
            $cachedResponses[$path] = $response;
            self::assertSame(
                $expectedStatus,
                $response['status'],
                'Unexpected status for ' . $path . '; body=' . (string) $response['body']
            );

            if ($expectedStatus === 422) {
                self::assertIsArray($response['json']);
                self::assertStringContainsStringIgnoringCase(
                    'wrong format',
                    (string) ($response['json']['message'] ?? '')
                );
                continue;
            }

            self::assertIsArray($response['json'], 'Expected JSON object for ' . $path);
            self::assertArrayHasKey('count', $response['json'], 'Missing count for ' . $path);
            self::assertArrayHasKey('records', $response['json'], 'Missing records for ' . $path);
            self::assertIsArray($response['json']['records'], 'Expected records array for ' . $path);
            self::assertSame(
                (int) $response['json']['count'],
                count($response['json']['records']),
                'Count/records mismatch for ' . $path
            );

            $records = $response['json']['records'];
            $count = (int) $response['json']['count'];
            $this->assertFilterSpecifics($path, $records, $count);
        }

        self::assertSame(
            (int) ($cachedResponses['/members/filter?removed=any']['json']['count'] ?? -1),
            (int) ($cachedResponses['/members/filter?removed=any&businessorsurname=']['json']['count'] ?? -2),
            'Expected empty businessorsurname to behave like removed=any'
        );
        self::assertSame(
            (int) ($cachedResponses['/members/filter']['json']['count'] ?? -1),
            (int) ($cachedResponses['/members/filter?removed=n']['json']['count'] ?? -2),
            'Expected blank filter to behave like removed=n default'
        );
    }

    private function assertFilterSpecifics(string $path, array $records, int $count): void
    {
        switch ($path) {
            case '/members/filter?surname=asfdkjhsakf':
                self::assertSame(0, $count);
                break;

            case '/members/filter?surname=cox':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertMatchesRegularExpression('/cox/i', (string) ($record['name'] ?? ''));
                }
                break;

            case '/members/filter?businessname=bonha':
                self::assertSame(1, $count);
                self::assertMatchesRegularExpression('/bonha/i', (string) ($records[0]['businessname'] ?? ''));
                break;

            case '/members/filter?email=jeffest':
                self::assertSame(1, $count);
                self::assertMatchesRegularExpression('/jeffest/i', (string) ($records[0]['email'] ?? ''));
                break;

            case '/members/filter?address=september&removed=y':
                self::assertGreaterThanOrEqual(1, $count);
                $joined = strtolower(
                    (string) ($records[0]['addressfirstline'] ?? '') . ' ' .
                    (string) ($records[0]['addresssecondline'] ?? '') . ' ' .
                    (string) ($records[0]['city'] ?? '')
                );
                self::assertStringContainsString('september', $joined);
                break;

            case '/members/filter?membertypeid=5':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertSame(5, (int) ($record['idmembership'] ?? 0));
                }
                break;

            case '/members/filter?membertyperange=5,6':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertContains((int) ($record['idmembership'] ?? 0), [5, 6]);
                }
                break;

            case '/members/filter?bankaccountid=1':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertStringContainsStringIgnoringCase('hsbc', (string) ($record['bankaccount'] ?? ''));
                }
                break;

            case '/members/filter?paymenttypeid=5':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertNotSame('', (string) ($record['paymenttype'] ?? ''));
                }
                break;

            case '/members/filter?removed=n':
            case '/members/filter':
                foreach ($records as $record) {
                    self::assertTrue(
                        !isset($record['deletedate']) || $record['deletedate'] === '' || $record['deletedate'] === null,
                        'Expected non-deleted record'
                    );
                }
                break;

            case '/members/filter?removed=y':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertNotSame('', (string) ($record['deletedate'] ?? ''));
                }
                break;

            case '/members/filter?hasemail=y':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertNotSame('', trim((string) ($record['email'] ?? '')));
                }
                break;

            case '/members/filter?hasemail=n':
                self::assertGreaterThanOrEqual(1, $count);
                foreach ($records as $record) {
                    self::assertSame('', trim((string) ($record['email'] ?? '')));
                }
                break;

            case '/members/filter?hasemail=any':
                self::assertGreaterThanOrEqual(1, $count);
                break;

            case '/members/filter?expirydatestart=2020-12-31':
                foreach ($records as $record) {
                    self::assertGreaterThanOrEqual('2020-12-31', (string) ($record['expirydate'] ?? ''));
                }
                break;

            case '/members/filter?expirydateend=2020-12-31':
                foreach ($records as $record) {
                    self::assertLessThanOrEqual('2020-12-31', (string) ($record['expirydate'] ?? ''));
                }
                break;

            case '/members/filter?reminderdatestart=2020-06-01&reminderdateend=2020-06-30':
                foreach ($records as $record) {
                    $date = (string) ($record['reminderdate'] ?? '');
                    self::assertGreaterThanOrEqual('2020-06-01', $date);
                    self::assertLessThanOrEqual('2020-06-30', $date);
                }
                break;

            case '/members/filter?updatedatestart=2021-01-01&updatedateend=2021-01-31&removed=any':
                self::assertEquals(5,$count);
                foreach ($records as $record) {
                    $date = substr((string) ($record['updatedate'] ?? ''), 0, 10);
                    self::assertGreaterThanOrEqual('2021-01-01', $date);
                    self::assertLessThanOrEqual('2021-01-31', $date);
                }
                break;

            case '/members/filter?lasttransactiondatestart=2025-12-01&lasttransactiondateend=2025-12-31':
                self::assertGreaterThanOrEqual(0, $count);
                foreach ($records as $record) {
                    $date = (string) ($record['lasttransactiondate'] ?? '');
                    self::assertGreaterThanOrEqual('2025-12-01', $date);
                    self::assertLessThanOrEqual('2025-12-31', $date);
                }
                break;

            case '/members/filter?deletedatestart=2020-06-01&deletedateend=2020-08-31':
                foreach ($records as $record) {
                    $date = (string) ($record['deletedate'] ?? '');
                    self::assertGreaterThanOrEqual('2020-06-01', $date);
                    self::assertLessThanOrEqual('2020-08-31', $date);
                }
                break;

            case '/members/filter?paymentmethod=cash':
            case '/members/filter?paymentmethod=cheq&lasttransactiondateend=2021-02-08':
            case '/members/filter?businessorsurname=car&removed=any':
            case '/members/filter?surname=bha&removed=any':
            case '/members/filter?removed=any':
            case '/members/filter?removed=any&businessorsurname=':
            default:
                self::assertGreaterThanOrEqual(0, $count);
                break;
        }
    }
}
