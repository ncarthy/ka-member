<?php

declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/Support/TestDatabase.php';
require_once __DIR__ . '/../tests/Support/ApiServer.php';
require_once __DIR__ . '/../tests/Support/HttpClient.php';

use Tests\Support\ApiServer;
use Tests\Support\HttpClient;
use Tests\Support\TestDatabase;

$keep = in_array('--keep', $argv, true);

function out(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

$db = new TestDatabase();
$server = null;
$schema = null;

try {
    out('Auth roundtrip probe starting...');
    $schema = $db->bootEphemeralSchema();
    out('Schema bootstrapped: ' . $schema);
    out('KA_DB_NAME (parent): ' . (getenv('KA_DB_NAME') ?: '<unset>'));
    out('KA_DB_NAME_FILE (parent): ' . (getenv('KA_DB_NAME_FILE') ?: '<unset>'));
    $dbNameFile = getenv('KA_DB_NAME_FILE');
    if ($dbNameFile && is_file($dbNameFile)) {
        out('KA_DB_NAME_FILE content: ' . trim((string) file_get_contents($dbNameFile)));
    }

    $server = new ApiServer();
    $server->start();
    out('Server started at: ' . $server->baseUrl());

    $client = new HttpClient($server->baseUrl());

    $auth = $client->request('POST', '/auth', [
        'body' => [
            'username' => 'ncarthy',
            'password' => 'treasurer',
        ],
    ]);

    out('POST /auth status=' . $auth['status']);
    out('POST /auth body=' . $auth['body']);

    $status = $client->request('GET', '/status');
    out('GET /status status=' . $status['status']);
    out('GET /status body=' . $status['body']);

    if ($keep) {
        out('--keep enabled: leaving server/database for inspection');
        exit(0);
    }

    $server->stop();
    out('Server stopped');

    $db->dropSchema();
    out('Schema dropped');
    exit(0);
} catch (Throwable $e) {
    out('FAIL: ' . get_class($e) . ': ' . $e->getMessage());

    if ($server !== null) {
        try {
            $server->stop();
        } catch (Throwable $ignored) {
        }
    }

    if ($schema !== null && !$keep) {
        try {
            $db->dropSchema();
        } catch (Throwable $ignored) {
        }
    }

    exit(1);
}
