<?php

namespace Core;

require_once __DIR__ . '/config.base.php';

// test profile
ini_set('display_errors', '1');
error_reporting(E_ALL);

Config::write('server', config_env_or_default('KA_SERVER', 'http://localhost'));
Config::write('api.path', config_env_or_default('KA_API_PATH', '/api/'));

// db defaults tuned for PHPUnit integration runs
Config::write('db.host', config_env_or_default('KA_DB_HOST', 'themis'));
Config::write('db.port', config_env_or_default('KA_DB_PORT', '3306'));
Config::write('db.name', config_db_name_for_runtime('ka_api_test'));
Config::write('db.user', config_env_or_default('KA_DB_USER_ENV', 'KA_DB_USER'));
Config::write('db.password', config_env_or_default('KA_DB_PASSWORD_ENV', 'KA_DB_PASSWORD'));

Config::write('password_attempts', 5);

Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', config_env_or_default('KA_TOKEN_ISS', 'https://knightsbridgeassociation.com'));
Config::write('token.aud', config_env_or_default('KA_TOKEN_AUD', 'https://member.knightsbridgeassociation.com'));
Config::write('token.envkeyname', config_env_or_default('KA_TOKEN_KEY_ENV', 'KA_MEMBER_KEY'));
Config::write('token.cookiename', config_env_or_default('KA_TOKEN_COOKIE_NAME', 'refreshToken'));
Config::write('token.cookiepath', config_env_or_default('KA_TOKEN_COOKIE_PATH', Config::read('api.path') . 'auth'));
Config::write('token.cookiesecure', filter_var(config_env_or_default('KA_TOKEN_COOKIE_SECURE', false), FILTER_VALIDATE_BOOLEAN));

Config::write('em.host', 'cp1.uk.netnerd.com');
Config::write('em.port', '465');
Config::write('em.user', 'member_admin+knightsbridgeassociation.com');
Config::write('em.replyto', 'membership@knightsbridgeassociation.com');
Config::write('em.password_envkeyname', 'EMAIL_PASSWORD');
Config::write('em.secure', true);

Config::write('gocardless.environment', config_env_or_default('GOCARDLESS_ENVIRONMENT', 'sandbox'));
Config::write('gocardless.webhook_secret', config_env_or_default('GOCARDLESS_WEBHOOK_SECRET_ENV', 'GOCARDLESS_WEBHOOK_SECRET'));
Config::write('gocardless.access_token', config_env_or_default('GOCARDLESS_ACCESS_TOKEN_ENV', 'GOCARDLESS_ACCESS_TOKEN'));
