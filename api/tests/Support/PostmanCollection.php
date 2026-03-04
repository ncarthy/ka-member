<?php

declare(strict_types=1);

namespace Tests\Support;

final class PostmanCollection
{
    private array $collection;

    public function __construct(string $path)
    {
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException('Unable to read Postman collection: ' . $path);
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid Postman collection JSON: ' . $path);
        }

        $this->collection = $decoded;
    }

    public function flattenRequests(): array
    {
        $requests = [];
        $items = $this->collection['item'] ?? [];
        $this->walkItems($items, '', $requests);

        return $requests;
    }

    public function classifyRequests(): array
    {
        $rows = $this->flattenRequests();

        return array_map(static function (array $row): array {
            $url = $row['url'] ?? '';
            $isInternal = str_starts_with($url, '{{url}}/');

            return [
                'name' => $row['name'],
                'method' => $row['method'],
                'url' => $url,
                'is_internal' => $isInternal,
                'has_tests' => $row['has_tests'],
                'has_prerequest' => $row['has_prerequest'],
                'expected_statuses' => $row['expected_statuses'],
            ];
        }, $rows);
    }

    private function walkItems(array $items, string $prefix, array &$requests): void
    {
        foreach ($items as $item) {
            $name = trim($prefix . '/' . ($item['name'] ?? ''), '/');

            if (isset($item['request'])) {
                $request = $item['request'];
                $tests = $this->extractEventScript($item, 'test');
                $pre = $this->extractEventScript($item, 'prerequest');
                $expectedStatuses = $this->extractExpectedStatuses($tests);

                $requests[] = [
                    'name' => $name,
                    'method' => $request['method'] ?? 'GET',
                    'url' => $request['url']['raw'] ?? '',
                    'body' => $request['body']['raw'] ?? null,
                    'tests_raw' => $tests,
                    'has_tests' => trim($tests) !== '',
                    'has_prerequest' => trim($pre) !== '',
                    'expected_statuses' => $expectedStatuses,
                ];
            }

            if (isset($item['item']) && is_array($item['item'])) {
                $this->walkItems($item['item'], $name, $requests);
            }
        }
    }

    private function extractEventScript(array $item, string $listenType): string
    {
        $events = $item['event'] ?? [];
        $scripts = [];

        foreach ($events as $event) {
            if (($event['listen'] ?? '') !== $listenType) {
                continue;
            }

            $exec = $event['script']['exec'] ?? [];
            if (is_array($exec)) {
                $scripts[] = implode("\n", $exec);
            }
        }

        return implode("\n", $scripts);
    }

    private function extractExpectedStatuses(string $tests): array
    {
        $statuses = [];

        if (preg_match_all('/to\\.have\\.status\\((\\d{3})\\)/', $tests, $matches)) {
            foreach ($matches[1] as $status) {
                $statuses[] = (int) $status;
            }
        }

        if (preg_match_all('/response\\.code\\)\\.to\\.eql\\((\\d{3})\\)/', $tests, $matches)) {
            foreach ($matches[1] as $status) {
                $statuses[] = (int) $status;
            }
        }

        $statuses = array_values(array_unique($statuses));
        sort($statuses);

        return $statuses;
    }
}
