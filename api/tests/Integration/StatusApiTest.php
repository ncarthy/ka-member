<?php

declare(strict_types=1);

namespace Tests\Integration;

final class StatusApiTest extends IntegrationTestCase
{
    public function test_status_read_routes_return_200(): void
    {
        $this->loginAdmin();

        $cases = [
            '/status',
            '/status/2',
            '/status/Corporate',
        ];

        foreach ($cases as $path) {
            $response = $this->client->request('GET', $path);
            self::assertSame(200, $response['status'], 'Expected 200 for ' . $path . '; body=' . (string) $response['body']);
        }
    }
}
