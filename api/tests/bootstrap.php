<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (getenv('KA_ENV') === false || getenv('KA_ENV') === '') {
    putenv('KA_ENV=test');
}

// Keep PHPUnit output clean: route application error_log() calls to a temp file.
if ((getenv('KA_TEST_SILENCE_ERROR_LOGS') ?: '1') === '1') {
    ini_set('log_errors', '1');
    ini_set('error_log', sys_get_temp_dir() . '/ka-api-phpunit-error.log');
}

// Load .env values for CLI test runs when environment variables are not already set.
$envPath = dirname(__DIR__) . '/.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
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
}

if (getenv('KA_MEMBER_KEY') === false || getenv('KA_MEMBER_KEY') === '') {
    putenv('KA_MEMBER_KEY=0123456789abcdef0123456789abcdef');
}

if (getenv('GOCARDLESS_WEBHOOK_SECRET') === false || getenv('GOCARDLESS_WEBHOOK_SECRET') === '') {
    putenv('GOCARDLESS_WEBHOOK_SECRET=test_webhook_secret');
}

if (getenv('GOCARDLESS_ACCESS_TOKEN') === false || getenv('GOCARDLESS_ACCESS_TOKEN') === '') {
    putenv('GOCARDLESS_ACCESS_TOKEN=test_access_token');
}
