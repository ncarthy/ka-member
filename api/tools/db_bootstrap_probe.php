<?php

declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../tests/Support/TestDatabase.php';

use Tests\Support\TestDatabase;

$keep = in_array('--keep', $argv, true);

function line(string $msg): void
{
    fwrite(STDOUT, $msg . PHP_EOL);
}

line('DB bootstrap probe starting...');
line('KA_DB_HOST=' . (getenv('KA_DB_HOST') ?: '<unset>'));
line('KA_DB_PORT=' . (getenv('KA_DB_PORT') ?: '<unset>'));
line('KA_DB_NAME=' . (getenv('KA_DB_NAME') ?: '<unset>'));
line('KA_TEST_DB_REUSE=' . (getenv('KA_TEST_DB_REUSE') ?: '<unset>'));
line('KA_DB_USER=' . (getenv('KA_DB_USER') ?: '<unset>'));

$db = new TestDatabase();
$schema = null;

try {
    line('Calling TestDatabase::bootEphemeralSchema() ...');
    $schema = $db->bootEphemeralSchema();
    line('SUCCESS: schema created/bootstrapped = ' . $schema);

    if ($keep) {
        line('Keeping schema (no drop) because --keep was provided.');
        exit(0);
    }

    line('Calling TestDatabase::dropSchema() ...');
    $db->dropSchema();
    line('SUCCESS: schema dropped');
    exit(0);
} catch (Throwable $e) {
    line('FAIL: ' . get_class($e) . ': ' . $e->getMessage());
    if ($schema !== null) {
        line('Schema at failure: ' . $schema);
    }
    exit(1);
}
