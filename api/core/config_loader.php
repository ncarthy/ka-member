<?php

$env = getenv('KA_ENV') ?: '';
if (strtolower($env) === 'test') {
    require_once __DIR__ . '/config.test.php';
} else {
    require_once __DIR__ . '/config.php';
}
