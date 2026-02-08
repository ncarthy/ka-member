<?php
/**
 * Load environment variables from .env file for CLI scripts
 * This makes environment variables available when running outside of Apache
 */

$envFile = dirname(__DIR__) . '/.env';

if (!file_exists($envFile)) {
    echo "Error: .env file not found at: $envFile\n";
    echo "Please create a .env file with your environment variables.\n";
    exit(1);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Skip comments
    if (strpos(trim($line), '#') === 0) {
        continue;
    }

    // Parse KEY=VALUE format
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes if present
        $value = trim($value, '"\'');

        // Set environment variable
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
