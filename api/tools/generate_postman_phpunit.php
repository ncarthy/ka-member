<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../tests/Support/PostmanCollection.php';

use Tests\Support\PostmanCollection;

$input = $argv[1] ?? null;
$output = $argv[2] ?? (__DIR__ . '/../tests/Integration/GeneratedPostmanStatusTest.php');

if ($input === null || !is_file($input)) {
    fwrite(STDERR, "Usage: php tools/generate_postman_phpunit.php <postman_collection.json> [output_file]\n");
    exit(1);
}

$collection = new PostmanCollection($input);
$rows = $collection->classifyRequests();

$cases = [];
foreach ($rows as $row) {
    if (!$row['is_internal'] || !$row['has_tests']) {
        continue;
    }

    $method = strtoupper((string) $row['method']);
    if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        continue;
    }

    $path = preg_replace('/^\{\{url\}\}/', '', (string) $row['url']);
    if ($path === null || $path === '' || str_contains($path, '{{')) {
        continue;
    }

    $statuses = $row['expected_statuses'];
    if ($statuses === []) {
        continue;
    }

    $cases[] = [
        'name' => $row['name'],
        'method' => $method,
        'path' => $path,
        'expected' => $statuses[0],
    ];
}

usort($cases, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));

$lines = [];
$lines[] = '<?php';
$lines[] = '';
$lines[] = 'declare(strict_types=1);';
$lines[] = '';
$lines[] = 'namespace Tests\\Integration;';
$lines[] = '';
$lines[] = 'final class GeneratedPostmanStatusTest extends IntegrationTestCase';
$lines[] = '{';
$lines[] = '    /**';
$lines[] = '     * @dataProvider postmanStatusCases';
$lines[] = '     */';
$lines[] = '    public function test_postman_status_case(string $name, string $method, string $path, int $expectedStatus): void';
$lines[] = '    {';
$lines[] = '        if (getenv(\'RUN_GENERATED_POSTMAN\') !== \'1\') {';
$lines[] = '            self::markTestSkipped(\'Generated Postman matrix is opt-in. Set RUN_GENERATED_POSTMAN=1 to execute.\');';
$lines[] = '        }';
$lines[] = '';
$lines[] = '        if (!str_starts_with($path, \'/auth\') && !str_starts_with($path, \'/webhook\')) {';
$lines[] = '            $this->loginAdmin();';
$lines[] = '        }';
$lines[] = '';
$lines[] = '        $response = $this->client->request($method, $path);';
$lines[] = '        self::assertSame($expectedStatus, $response[\'status\'], $name . " failed for " . $path);';
$lines[] = '    }';
$lines[] = '';
$lines[] = '    public static function postmanStatusCases(): array';
$lines[] = '    {';
$lines[] = '        return [';

foreach ($cases as $case) {
    $lines[] = sprintf(
        "            '%s' => ['%s', '%s', '%s', %d],",
        addslashes($case['name']),
        addslashes($case['name']),
        $case['method'],
        addslashes($case['path']),
        $case['expected']
    );
}

$lines[] = '        ];';
$lines[] = '    }';
$lines[] = '}';
$lines[] = '';

file_put_contents($output, implode(PHP_EOL, $lines));

fwrite(STDOUT, sprintf("Generated %d status cases to %s\n", count($cases), $output));
