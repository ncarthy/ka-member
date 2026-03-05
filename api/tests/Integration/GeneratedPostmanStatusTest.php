<?php

declare(strict_types=1);

namespace Tests\Integration;

final class GeneratedPostmanStatusTest extends IntegrationTestCase
{
    /**
     * @dataProvider postmanStatusCases
     */
    public function test_postman_status_case(string $name, string $method, string $path, int $expectedStatus): void
    {
        if (getenv('RUN_GENERATED_POSTMAN') !== '1') {
            self::markTestSkipped('Generated Postman matrix is opt-in. Set RUN_GENERATED_POSTMAN=1 to execute.');
        }

        if (!str_starts_with($path, '/auth') && !str_starts_with($path, '/webhook')) {
            $this->loginAdmin();
        }

        $response = $this->client->request($method, $path);
        self::assertSame($expectedStatus, $response['status'], $name . " failed for " . $path);
    }

    public static function postmanStatusCases(): array
    {
        return [
            'Pending/Member - Create Pending' => ['Pending/Member - Create Pending', 'POST', '/member', 200],
            'Pending/Name - Create for Pending' => ['Pending/Name - Create for Pending', 'POST', '/name', 200],
            'Pending/Transaction - Create Pending' => ['Pending/Transaction - Create Pending', 'POST', '/transaction', 200],
            'Transaction/Transaction - Create' => ['Transaction/Transaction - Create', 'POST', '/transaction', 200],
            'Transaction/Transaction - Delete By idmember' => ['Transaction/Transaction - Delete By idmember', 'DELETE', '/transaction/idmember/509', 200],
            'Transaction/Transaction - Read All' => ['Transaction/Transaction - Read All', 'GET', '/transaction', 200],
            'Transaction/Transaction - Read One id' => ['Transaction/Transaction - Read One id', 'GET', '/transaction/3150', 200],
            'Transaction/Transaction - Read One invalid ID' => ['Transaction/Transaction - Read One invalid ID', 'GET', '/transaction/1', 422],
            'Transaction/Transaction - Read idmember' => ['Transaction/Transaction - Read idmember', 'GET', '/transaction/idmember/8', 200],
            'Transaction/Transaction - Verify Delete by idmember' => ['Transaction/Transaction - Verify Delete by idmember', 'GET', '/transaction/idmember/509', 200],
            'Transactions/Transactions: Detail' => ['Transactions/Transactions: Detail', 'GET', '/transactions/detail?month=01&year=2021&bankID=3', 200],
            'Transactions/Transactions: Summary By Month' => ['Transactions/Transactions: Summary By Month', 'GET', '/transactions/summary?start=2021-01-01&end=2021-03-31', 200],
            'Transactions/Transactions: Summary By Month Copy' => ['Transactions/Transactions: Summary By Month Copy', 'GET', '/transactions/summary?start=2021-01-01&end=&bankID=3', 200],
            'Users/Users - Create New' => ['Users/Users - Create New', 'POST', '/user', 200],
            'Users/Users - Get ID of New User' => ['Users/Users - Get ID of New User', 'GET', '/user', 200],
            'Users/Users - Password Complexity' => ['Users/Users - Password Complexity', 'POST', '/user', 422],
            'Users/Users - Read All' => ['Users/Users - Read All', 'GET', '/user', 200],
            'Users/Users - Read All - Test User' => ['Users/Users - Read All - Test User', 'GET', '/user', 401],
            'Users/Users - Read One' => ['Users/Users - Read One', 'GET', '/user/5', 200],
            'Users/Users - Read One -Test User, another\'s details' => ['Users/Users - Read One -Test User, another\'s details', 'GET', '/user/5', 401],
            'Users/Users - Read One -Test User, own details' => ['Users/Users - Read One -Test User, own details', 'GET', '/user/20', 200],
            'Users/Users - Read One -Test User, own details 2' => ['Users/Users - Read One -Test User, own details 2', 'GET', '/user/20', 200],
            'Users/Users - Read One Invalid ID' => ['Users/Users - Read One Invalid ID', 'GET', '/user/5000', 422],
            'Users/Users - Update Copy - Test User, another\'s data' => ['Users/Users - Update Copy - Test User, another\'s data', 'PUT', '/user/6', 401],
            'Users/Users - Update Copy - Test User, own data' => ['Users/Users - Update Copy - Test User, own data', 'PUT', '/user/20', 200],
            'gocardless/Another Payment' => ['gocardless/Another Payment', 'POST', '/webhook/gocardless', 200],
            'gocardless/Invalid Signature' => ['gocardless/Invalid Signature', 'POST', '/webhook/gocardless', 401],
            'gocardless/Missing Signature' => ['gocardless/Missing Signature', 'POST', '/webhook/gocardless', 400],
            'gocardless/New Mandate' => ['gocardless/New Mandate', 'POST', '/webhook/gocardless', 200],
            'gocardless/Payment Confirmed' => ['gocardless/Payment Confirmed', 'POST', '/webhook/gocardless', 200],
            'gocardless/Signature Validation' => ['gocardless/Signature Validation', 'POST', '/webhook/gocardless', 200],
            'gocardless/Test Route' => ['gocardless/Test Route', 'POST', '/webhook/gocardless', 400],
        ];
    }
}
