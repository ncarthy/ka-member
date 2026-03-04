<?php

declare(strict_types=1);

/**
 * PHPUnit integration preflight for DB permissions/connectivity.
 *
 * Usage:
 *   php tools/phpunit_preflight.php
 *
 * Exit codes:
 *   0 = preflight passed
 *   1 = preflight failed
 */

function out(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function fail(string $message): void
{
    fwrite(STDERR, '[FAIL] ' . $message . PHP_EOL);
}

function ok(string $message): void
{
    out('[OK] ' . $message);
}

function warn(string $message): void
{
    out('[WARN] ' . $message);
}

function loadEnvFile(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

function envOr(string $key, string $fallback): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $fallback;
    }

    return $value;
}

$root = dirname(__DIR__);
loadEnvFile($root . '/.env');

require_once $root . '/core/config.php';

$defaultHost = \Core\Config::read('db.host') ?: '127.0.0.1';
$defaultPort = \Core\Config::read('db.port') ?: '3306';

$host = envOr('KA_DB_HOST', (string) $defaultHost);
$port = envOr('KA_DB_PORT', (string) $defaultPort);
$user = getenv('KA_DB_USER') ?: '';
$password = getenv('KA_DB_PASSWORD');
$reuse = filter_var(envOr('KA_TEST_DB_REUSE', '0'), FILTER_VALIDATE_BOOLEAN);
$reuseDb = getenv('KA_DB_NAME') ?: '';

out('PHPUnit DB preflight');
out('  host=' . $host . ' port=' . $port . ' user=' . ($user !== '' ? $user : '<missing>'));
out('  mode=' . ($reuse ? 'reuse schema' : 'ephemeral schema'));

$errors = 0;

if ($user === '') {
    fail('KA_DB_USER is missing');
    $errors++;
}

if ($password === false) {
    fail('KA_DB_PASSWORD is missing');
    $errors++;
}

if ($reuse && $reuseDb === '') {
    fail('KA_DB_NAME must be set when KA_TEST_DB_REUSE=1');
    $errors++;
}

if ($errors > 0) {
    exit(1);
}

try {
    $adminDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
    $pdo = new PDO($adminDsn, $user, (string) $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    ok('Connected to MySQL server');
} catch (Throwable $e) {
    fail('Could not connect to MySQL: ' . $e->getMessage());
    exit(1);
}

try {
    $row = $pdo->query('SELECT CURRENT_USER() AS current_user')->fetch();
    if (is_array($row) && isset($row['current_user'])) {
        out('  current_user=' . $row['current_user']);
    }
} catch (Throwable $e) {
    warn('Unable to read CURRENT_USER(): ' . $e->getMessage());
}

try {
    $grantRows = $pdo->query('SHOW GRANTS')->fetchAll();
    if (is_array($grantRows) && count($grantRows) > 0) {
        out('  grants:');
        foreach ($grantRows as $grantRow) {
            foreach ($grantRow as $grant) {
                out('    - ' . $grant);
            }
        }
    }
} catch (Throwable $e) {
    warn('Could not run SHOW GRANTS (permission may be restricted): ' . $e->getMessage());
}

if ($reuse) {
    try {
        $schemaDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $reuseDb);
        $schemaPdo = new PDO($schemaDsn, $user, (string) $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        ok('Connected to reuse schema `' . $reuseDb . '`');

        $schemaPdo->exec('CREATE TEMPORARY TABLE __ka_preflight_tmp (id INT NOT NULL)');
        $schemaPdo->exec('INSERT INTO __ka_preflight_tmp (id) VALUES (1)');
        $schemaPdo->query('SELECT COUNT(*) AS c FROM __ka_preflight_tmp')->fetch();
        $schemaPdo->exec('DROP TEMPORARY TABLE __ka_preflight_tmp');
        ok('Reuse schema write + temporary table checks passed');
    } catch (Throwable $e) {
        fail('Reuse schema checks failed: ' . $e->getMessage());
        $errors++;
    }
} else {
    $probeDb = sprintf('ka_api_test_preflight_%d_%d', time(), getmypid());

    try {
        $pdo->exec('CREATE DATABASE `' . $probeDb . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        ok('CREATE DATABASE permission verified');

        $schemaDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $probeDb);
        $schemaPdo = new PDO($schemaDsn, $user, (string) $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $schemaPdo->exec('CREATE TABLE preflight_table (id INT NOT NULL PRIMARY KEY)');
        $schemaPdo->exec('CREATE OR REPLACE VIEW preflight_view AS SELECT id FROM preflight_table');
        $schemaPdo->exec('INSERT INTO preflight_table (id) VALUES (1)');
        $schemaPdo->query('SELECT * FROM preflight_view')->fetchAll();
        ok('Ephemeral schema DDL/DML/view checks passed');
    } catch (Throwable $e) {
        fail('Ephemeral schema checks failed: ' . $e->getMessage());
        $errors++;
    }

    try {
        $pdo->exec('DROP DATABASE IF EXISTS `' . $probeDb . '`');
        ok('DROP DATABASE permission verified');
    } catch (Throwable $e) {
        fail('DROP DATABASE failed for preflight schema `' . $probeDb . '`: ' . $e->getMessage());
        $errors++;
    }
}

if ($errors > 0) {
    fail('Preflight failed with ' . $errors . ' issue(s).');
    exit(1);
}

ok('Preflight passed. Integration test prerequisites look good.');
exit(0);

