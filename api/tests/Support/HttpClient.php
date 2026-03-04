<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

final class HttpClient
{
    private string $baseUrl;
    private array $cookies = [];
    private ?string $bearerToken = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function setBearerToken(string $token): void
    {
        $this->bearerToken = $token;
    }

    public function clearBearerToken(): void
    {
        $this->bearerToken = null;
    }

    public function request(string $method, string $path, array $options = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;

        if ($this->bearerToken !== null && !isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        }

        if (!empty($this->cookies)) {
            $headers['Cookie'] = $this->buildCookieHeader();
        }

        if (is_array($body)) {
            $body = json_encode($body);
            $headers['Content-Type'] = 'application/json';
        }

        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        $responseHeaders = [];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADERFUNCTION => function ($curl, string $headerLine) use (&$responseHeaders): int {
                $trimmed = trim($headerLine);
                if ($trimmed !== '') {
                    $responseHeaders[] = $trimmed;
                }
                return strlen($headerLine);
            },
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_TIMEOUT => 10,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            throw new RuntimeException('HTTP request failed: ' . curl_error($ch));
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $this->captureCookies($responseHeaders);

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $responseBody,
            'json' => $this->decodeJson($responseBody),
        ];
    }

    private function decodeJson(string $body): ?array
    {
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    private function captureCookies(array $headers): void
    {
        foreach ($headers as $headerLine) {
            if (stripos($headerLine, 'Set-Cookie:') !== 0) {
                continue;
            }

            $cookiePart = trim(substr($headerLine, strlen('Set-Cookie:')));
            $pairs = explode(';', $cookiePart);
            $nameValue = explode('=', trim($pairs[0]), 2);
            if (count($nameValue) === 2) {
                $this->cookies[$nameValue[0]] = $nameValue[1];
            }
        }
    }

    private function buildCookieHeader(): string
    {
        $parts = [];
        foreach ($this->cookies as $name => $value) {
            $parts[] = $name . '=' . $value;
        }

        return implode('; ', $parts);
    }
}
