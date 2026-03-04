<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;
use ReflectionClass;
use RuntimeException;

final class TestDatabase
{
    private ?PDO $admin = null;
    private ?PDO $schemaPdo = null;
    private ?string $schemaName = null;
    private bool $ownsSchema = false;
    private string $dbNameFile;

    public function __construct()
    {
        $this->dbNameFile = getenv('KA_DB_NAME_FILE') ?: (sys_get_temp_dir() . '/ka-api-active-db-name.txt');
    }

    public function bootEphemeralSchema(): string
    {
        require_once dirname(__DIR__, 2) . '/core/config_loader.php';

        $defaultHost = class_exists('Core\\Config') ? (\Core\Config::read('db.host') ?: '127.0.0.1') : '127.0.0.1';
        $defaultPort = class_exists('Core\\Config') ? (\Core\Config::read('db.port') ?: '3306') : '3306';

        $host = getenv('KA_DB_HOST') ?: $defaultHost;
        $port = getenv('KA_DB_PORT') ?: $defaultPort;
        $user = getenv('KA_DB_USER');
        $password = getenv('KA_DB_PASSWORD');

        if ($user === false || $user === '' || $password === false) {
            throw new RuntimeException('KA_DB_USER and KA_DB_PASSWORD must be set for integration tests');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
        $this->admin = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $reuseSchema = filter_var(getenv('KA_TEST_DB_REUSE') ?: '0', FILTER_VALIDATE_BOOLEAN);
        if ($reuseSchema) {
            $configuredDbName = getenv('KA_DB_NAME') ?: '';
            if ($configuredDbName === '') {
                throw new RuntimeException('KA_DB_NAME must be set when KA_TEST_DB_REUSE=1');
            }

            $this->schemaName = $configuredDbName;
            // Ensure reusable schema exists before connecting to it.
            $this->admin->exec('CREATE DATABASE IF NOT EXISTS `' . $this->schemaName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $this->ownsSchema = false;
        } else {
            $this->schemaName = sprintf('ka_api_test_%d_%d', time(), getmypid());
            $this->admin->exec('CREATE DATABASE `' . $this->schemaName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $this->ownsSchema = true;
        }

        putenv('KA_DB_NAME=' . $this->schemaName);
        putenv('KA_DB_NAME_FILE=' . $this->dbNameFile);
        // Persist resolved DB settings so child server process uses the exact same connection target.
        putenv('KA_DB_HOST=' . $host);
        putenv('KA_DB_PORT=' . $port);
        putenv('KA_DB_USER=' . $user);
        putenv('KA_DB_PASSWORD=' . (string) $password);
        $_ENV['KA_DB_HOST'] = $host;
        $_ENV['KA_DB_PORT'] = $port;
        $_ENV['KA_DB_USER'] = $user;
        $_ENV['KA_DB_PASSWORD'] = (string) $password;
        $_ENV['KA_DB_NAME'] = $this->schemaName;
        $_ENV['KA_DB_NAME_FILE'] = $this->dbNameFile;
        $_SERVER['KA_DB_HOST'] = $host;
        $_SERVER['KA_DB_PORT'] = $port;
        $_SERVER['KA_DB_USER'] = $user;
        $_SERVER['KA_DB_PASSWORD'] = (string) $password;
        $_SERVER['KA_DB_NAME'] = $this->schemaName;
        $_SERVER['KA_DB_NAME_FILE'] = $this->dbNameFile;
        @file_put_contents($this->dbNameFile, $this->schemaName);

        $schemaDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $this->schemaName);
        $this->schemaPdo = new PDO($schemaDsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->importSqlFile(dirname(__DIR__) . '/sql/schema.sql');
        $this->importSqlFile(dirname(__DIR__) . '/sql/seed_reference.sql');
        $this->importSqlFile(dirname(__DIR__) . '/sql/seed_auth.sql');

        $this->resetDatabaseSingleton();

        return $this->schemaName;
    }

    public function resetMutableData(): void
    {
        if ($this->schemaPdo === null) {
            return;
        }

        $tables = [
            'usertoken',
            'webhook_queue',
            'webhook_log',
            'transaction',
            'membername',
            'gocardless_mandate',
            'member',
        ];

        $this->schemaPdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $this->schemaPdo->exec('TRUNCATE TABLE `' . $table . '`');
        }
        $this->schemaPdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->importSqlFile(dirname(__DIR__) . '/sql/seed_reference.sql');
        $this->importSqlFile(dirname(__DIR__) . '/sql/seed_auth.sql');

        $this->resetDatabaseSingleton();
    }

    public function dropSchema(): void
    {
        if ($this->admin !== null && $this->schemaName !== null && $this->ownsSchema) {
            try {
                $this->admin->exec('DROP DATABASE IF EXISTS `' . $this->schemaName . '`');
            } catch (\Throwable $e) {
                $ignoreDropErrors = filter_var(getenv('KA_TEST_IGNORE_DROP_ERRORS') ?: '1', FILTER_VALIDATE_BOOLEAN);
                if (!$ignoreDropErrors) {
                    throw $e;
                }
            }
        }

        $this->schemaPdo = null;
        $this->admin = null;
        $this->schemaName = null;
        $this->ownsSchema = false;
        if (is_file($this->dbNameFile)) {
            @unlink($this->dbNameFile);
        }
        $this->resetDatabaseSingleton();
    }

    private function importSqlFile(string $path): void
    {
        if ($this->schemaPdo === null) {
            throw new RuntimeException('Schema PDO is not initialised');
        }

        if (!is_file($path)) {
            throw new RuntimeException('SQL file not found: ' . $path);
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Unable to read SQL file: ' . $path);
        }

        $this->schemaPdo->exec($sql);
    }

    private function resetDatabaseSingleton(): void
    {
        if (!class_exists('Core\\Database')) {
            return;
        }

        $reflection = new ReflectionClass('Core\\Database');
        if (!$reflection->hasProperty('instance')) {
            return;
        }

        $property = $reflection->getProperty('instance');
        $property->setValue(null, null);
    }
}

