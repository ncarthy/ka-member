<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

final class ApiServer
{
    private string $host;
    private int $port;
    private $process;
    private array $pipes = [];

    public function __construct(string $host = '127.0.0.1')
    {
        $this->host = $host;
        $this->port = $this->findFreePort();
    }

    public function start(): void
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['file', sys_get_temp_dir() . '/ka-api-php-server.out.log', 'a'],
            2 => ['file', sys_get_temp_dir() . '/ka-api-php-server.err.log', 'a'],
        ];

        $env = $this->buildServerEnv();
        $command = $this->buildServerCommand($env);
        $this->process = proc_open($command, $descriptorSpec, $this->pipes, dirname(__DIR__, 2), $env);

        if (!is_resource($this->process)) {
            throw new RuntimeException('Unable to start PHP built-in server');
        }

        $this->waitUntilReady();
    }

    public function stop(): void
    {
        if (!is_resource($this->process)) {
            return;
        }

        proc_terminate($this->process);
        proc_close($this->process);
        $this->process = null;
    }

    public function baseUrl(): string
    {
        return sprintf('http://%s:%d', $this->host, $this->port);
    }

    private function waitUntilReady(): void
    {
        $start = microtime(true);

        while ((microtime(true) - $start) < 10.0) {
            $ctx = stream_context_create([
                'http' => ['ignore_errors' => true, 'timeout' => 1],
            ]);

            $response = @file_get_contents($this->baseUrl() . '/status', false, $ctx);
            if ($response !== false || !empty($http_response_header)) {
                return;
            }

            usleep(100000);
        }

        throw new RuntimeException('PHP built-in server did not become ready in time');
    }

    private function buildServerEnv(): array
    {
        $env = [];

        // Carry current process env through explicitly (Windows proc_open can be inconsistent otherwise).
        $baseEnv = getenv();
        if (is_array($baseEnv)) {
            $env = $baseEnv;
        }

        // Ensure app-critical env vars are always present in the child server process.
        $keys = [
            'KA_DB_HOST',
            'KA_DB_PORT',
            'KA_DB_NAME',
            'KA_DB_NAME_FILE',
            'KA_DB_USER',
            'KA_DB_PASSWORD',
            'KA_DB_USER_ENV',
            'KA_DB_PASSWORD_ENV',
            'KA_MEMBER_KEY',
            'KA_TOKEN_ISS',
            'KA_TOKEN_AUD',
            'KA_TOKEN_KEY_ENV',
            'GOCARDLESS_ENVIRONMENT',
            'GOCARDLESS_WEBHOOK_SECRET',
            'GOCARDLESS_WEBHOOK_SECRET_ENV',
            'GOCARDLESS_ACCESS_TOKEN',
            'GOCARDLESS_ACCESS_TOKEN_ENV',
            'KA_TOKEN_COOKIE_SECURE',
            'KA_API_PATH',
            'KA_SERVER',
        ];

        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $env[$key] = $value;
            }
        }

        // Avoid xdebug debugger connection delays/noise in HTTP integration tests.
        $env['XDEBUG_MODE'] = 'off';

        return $env;
    }

    private function buildServerCommand(array $env): string
    {
        $phpServerCommand = sprintf('php -S %s:%d -t . index.php', $this->host, $this->port);

        $keys = [
            'KA_DB_HOST',
            'KA_DB_PORT',
            'KA_DB_NAME',
            'KA_DB_NAME_FILE',
            'KA_DB_USER',
            'KA_DB_PASSWORD',
            'KA_DB_USER_ENV',
            'KA_DB_PASSWORD_ENV',
            'KA_MEMBER_KEY',
            'KA_TOKEN_ISS',
            'KA_TOKEN_AUD',
            'KA_TOKEN_KEY_ENV',
            'GOCARDLESS_ENVIRONMENT',
            'GOCARDLESS_WEBHOOK_SECRET',
            'GOCARDLESS_WEBHOOK_SECRET_ENV',
            'GOCARDLESS_ACCESS_TOKEN',
            'GOCARDLESS_ACCESS_TOKEN_ENV',
            'KA_TOKEN_COOKIE_SECURE',
            'KA_API_PATH',
            'KA_SERVER',
            'XDEBUG_MODE',
        ];

        $setSegments = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $env)) {
                continue;
            }

            $value = str_replace('"', '\"', (string) $env[$key]);
            $setSegments[] = sprintf('set "%s=%s"', $key, $value);
        }

        if (DIRECTORY_SEPARATOR !== '\\') {
            $exportSegments = [];
            foreach ($keys as $key) {
                if (!array_key_exists($key, $env)) {
                    continue;
                }
                $escaped = str_replace("'", "'\\''", (string) $env[$key]);
                $exportSegments[] = sprintf("export %s='%s'", $key, $escaped);
            }
            if (empty($exportSegments)) {
                return $phpServerCommand;
            }
            return implode(' && ', $exportSegments) . ' && ' . $phpServerCommand;
        }
        if (empty($setSegments)) {
            return $phpServerCommand;
        }

        return implode(' && ', $setSegments) . ' && ' . $phpServerCommand;
    }

    private function findFreePort(): int
    {
        for ($port = 18080; $port < 18180; $port++) {
            $socket = @stream_socket_server(sprintf('tcp://%s:%d', $this->host, $port), $errno, $errstr);
            if ($socket !== false) {
                fclose($socket);
                return $port;
            }
        }

        throw new RuntimeException('Unable to find a free localhost port for test server');
    }
}
