<?php

namespace Core;

if (!class_exists(__NAMESPACE__ . '\\Config', false)) {
    // Config pattern from https://stackoverflow.com/a/2047999/6941165
    class Config
    {
        static $confArray;

        public static function read($name)
        {
            return self::$confArray[$name] ?? null;
        }

        public static function write($name, $value)
        {
            self::$confArray[$name] = $value;
        }
    }
}

if (!function_exists(__NAMESPACE__ . '\\config_env_or_default')) {
    function config_env_or_default($key, $default)
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists(__NAMESPACE__ . '\\config_db_name_for_runtime')) {
    function config_db_name_for_runtime(string $default): string
    {
        $dbNameFile = config_env_or_default('KA_DB_NAME_FILE', sys_get_temp_dir() . '/ka-api-active-db-name.txt');
        if ($dbNameFile !== '' && is_file($dbNameFile) && is_readable($dbNameFile)) {
            $candidate = trim((string) file_get_contents($dbNameFile));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return config_env_or_default('KA_DB_NAME', $default);
    }
}
