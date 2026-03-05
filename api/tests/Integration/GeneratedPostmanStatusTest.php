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
            'Members/Members - Anonymize Old Former Members' => ['Members/Members - Anonymize Old Former Members', 'PATCH', '/members/oldformermember/60', 200],
            'Members/Members - CEM' => ['Members/Members - CEM', 'GET', '/members/cem', 200],
            'Members/Members - CSV Mailing List' => ['Members/Members - CSV Mailing List', 'GET', '/members/mailinglist', 200],
            'Members/Members - Count' => ['Members/Members - Count', 'GET', '/members/summary', 200],
            'Members/Members - Discount' => ['Members/Members - Discount', 'GET', '/members/discount', 200],
            'Members/Members - Duplicate payers' => ['Members/Members - Duplicate payers', 'GET', '/members/duplicatepayers', 200],
            'Members/Members - Email List' => ['Members/Members - Email List', 'GET', '/members/emaillist', 200],
            'Members/Members - Former Member 12m' => ['Members/Members - Former Member 12m', 'GET', '/members/formermember/18', 200],
            'Members/Members - Invalid Emails' => ['Members/Members - Invalid Emails', 'GET', '/members/invalidemails', 200],
            'Members/Members - Invalid Postcodes' => ['Members/Members - Invalid Postcodes', 'GET', '/members/invalidpostcodes', 200],
            'Members/Members - Invalid Report Name' => ['Members/Members - Invalid Report Name', 'GET', '/members/blahblah', 404],
            'Members/Members - Lapsed' => ['Members/Members - Lapsed', 'GET', '/members/lapsed/18', 200],
            'Members/Members - Lapsed CEM 18mocutoff' => ['Members/Members - Lapsed CEM 18mocutoff', 'GET', '/members/lapsedcem/18', 200],
            'Members/Members - Lapsed CEM Set To Former' => ['Members/Members - Lapsed CEM Set To Former', 'PATCH', '/members/lapsedcem/10', 200],
            'Members/Members - Lapsed CEM all' => ['Members/Members - Lapsed CEM all', 'GET', '/members/lapsedcem/60', 200],
            'Members/Members - Life/Hon' => ['Members/Members - Life/Hon', 'GET', '/members/life_and_hon', 200],
            'Members/Members - Life/Hon Copy' => ['Members/Members - Life/Hon Copy', 'GET', '/members/filter?membertyperange=(5,6)', 200],
            'Members/Members - Map List' => ['Members/Members - Map List', 'GET', '/members/maplist', 200],
            'Members/Members - No Email CSV List' => ['Members/Members - No Email CSV List', 'GET', '/members/noemaillist', 200],
            'Members/Members - No UK Address' => ['Members/Members - No UK Address', 'GET', '/members/noukaddress', 200],
            'Members/Members - Old Former Member' => ['Members/Members - Old Former Member', 'GET', '/members/oldformermember/36', 200],
            'Members/Members - Paying Hon Life' => ['Members/Members - Paying Hon Life', 'GET', '/members/payinghonlife', 200],
            'Name/Name - Check delete on worked' => ['Name/Name - Check delete on worked', 'GET', '/name/6594', 422],
            'Name/Name - Check idmember Update' => ['Name/Name - Check idmember Update', 'GET', '/name/idmember/119', 200],
            'Name/Name - Create' => ['Name/Name - Create', 'POST', '/name', 200],
            'Name/Name - Delete a single name' => ['Name/Name - Delete a single name', 'DELETE', '/name/6594', 200],
            'Name/Name - Delete all names for member' => ['Name/Name - Delete all names for member', 'DELETE', '/name/idmember/419', 200],
            'Name/Name - Read One - No ID' => ['Name/Name - Read One - No ID', 'GET', '/name', 404],
            'Name/Name - Read One - invalid ID' => ['Name/Name - Read One - invalid ID', 'GET', '/name/100', 422],
            'Name/Name - Read One - invalid ID Copy' => ['Name/Name - Read One - invalid ID Copy', 'GET', '/name/idmember/100000', 200],
            'Name/Name - Read One ID' => ['Name/Name - Read One ID', 'GET', '/name/6475', 200],
            'Name/Name - Read from idmember' => ['Name/Name - Read from idmember', 'GET', '/name/idmember/119', 200],
            'Name/Name - Test names are deleted' => ['Name/Name - Test names are deleted', 'GET', '/name/idmember/419', 200],
            'Name/Name - Update Names for Idmember' => ['Name/Name - Update Names for Idmember', 'PUT', '/name/idmember/119', 200],
            'PaymentType/PaymentType - Read' => ['PaymentType/PaymentType - Read', 'GET', '/payment_type', 200],
            'PaymentType/PaymentType - Read One ID Copy' => ['PaymentType/PaymentType - Read One ID Copy', 'GET', '/payment_type/2', 200],
            'PaymentType/PaymentType - Read One Name' => ['PaymentType/PaymentType - Read One Name', 'GET', '/payment_type/cash', 200],
            'Pending/Member - Create Pending' => ['Pending/Member - Create Pending', 'POST', '/member', 200],
            'Pending/Name - Create for Pending' => ['Pending/Name - Create for Pending', 'POST', '/name', 200],
            'Pending/Transaction - Create Pending' => ['Pending/Transaction - Create Pending', 'POST', '/transaction', 200],
            'Status/Status - Read All' => ['Status/Status - Read All', 'GET', '/status', 200],
            'Status/Status - Read One ID' => ['Status/Status - Read One ID', 'GET', '/status/2', 200],
            'Status/Status - Read One Name' => ['Status/Status - Read One Name', 'GET', '/status/Corporate', 200],
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
            'dB Sanity Check/SanityCheck: Deleted but Not Former or CEM' => ['dB Sanity Check/SanityCheck: Deleted but Not Former or CEM', 'GET', '/members/deletedbutnotformer', 200],
            'dB Sanity Check/SanityCheck: lapsed members' => ['dB Sanity Check/SanityCheck: lapsed members', 'GET', '/members/lapsed/24', 200],
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
