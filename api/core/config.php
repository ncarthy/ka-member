<?php

namespace Core;

// development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// production
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Config pattern from https://stackoverflow.com/a/2047999/6941165
class Config
{
    static $confArray;

    public static function read($name)
    {
        return self::$confArray[$name];
    }

    public static function write($name, $value)
    {
        self::$confArray[$name] = $value;
    }

}

// Note
// Environment key values (such as 'KA_DB_PASSWORD') are stored in:
// Development: C:\Apache24\conf\httpd.conf
// Production: ~/admin.knightsbridgeassociation.co.uk/api/.htaccess

// server location (for Access-Origin)
Config::write('server', 'http://localhost:4200'); // Must change when deploying to production
Config::write('api.path', '/api/');

// db
Config::write('db.host', 'themis');             // Database IP or hostname. Usually 'localhost' on produciton
Config::write('db.port', '3306');               // standard MySql / MariaDB port
Config::write('db.name', 'knightsb_membership'); // Database name
Config::write('db.user', 'KA_DB_USER');      // Database user. All database actions are performed by this single user.
Config::write('db.password', 'KA_DB_PASSWORD');    // Database user's password is stored as enviornment variable


// number of allowed password attempts. User is suspended if fails to login 6 times in a row
Config::write('password_attempts', 5);

// token settings
Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', 'https://knightsbridgeassociation.com');
Config::write('token.aud', 'https://member.knightsbridgeassociation.com');
Config::write('token.envkeyname', 'KA_MEMBER_KEY'); // Make sure the EnvVar is a 32 character length string
Config::write('token.cookiename', 'refreshToken');
Config::write('token.cookiepath', Config::read('api.path') . 'auth');
Config::write('token.cookiesecure', false);

// email
Config::write('em.host', 'cp1.uk.netnerd.com');
Config::write('em.port', '465');
Config::write('em.user', 'member_admin+knightsbridgeassociation.com');
Config::write('em.replyto', 'membership@knightsbridgeassociation.com');
Config::write('em.password_envkeyname', 'EMAIL_PASSWORD');
Config::write('em.secure', true);

// gocardless
Config::write('gocardless.environment', 'sandbox'); // 'live' for production, 'sandbox' for testing
Config::write('gocardless.webhook_secret', 'GOCARDLESS_WEBHOOK_SECRET');
Config::write('gocardless.access_token', 'GOCARDLESS_ACCESS_TOKEN');
