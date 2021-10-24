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

// server location (for Access-Origin)
Config::write('server', 'http://localhost:4200');
Config::write('api.path', '/api/');

// db
Config::write('db.host', '192.168.1.26');
Config::write('db.port', '3306');
Config::write('db.name', 'knightsb_membership2');
Config::write('db.user', 'knightsb_member');
Config::write('db.password', 'SsP4qIm4omu4M');

// number of allowed password attempts
Config::write('password_attempts', 5);

// token settings
Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', 'https://knightsbridgeassociation.com');
Config::write('token.aud', 'https://member.knightsbridgeassociation.com');
Config::write('token.envkeyname', 'KA_MEMBER_KEY');
Config::write('token.cookiename', 'refreshToken');
Config::write('token.cookiepath', Config::read('api.path') . 'auth');
Config::write('token.cookiesecure', false);

// email
Config::write('em.host', 'uk1.cp.netnerd.com');
Config::write('em.port', '465');
Config::write('em.user', 'member_admin+knightsbridgeassociation.com');
Config::write('em.replyto', 'membership@knightsbridgeassociation.com');
Config::write('em.password_envkeyname', 'EMAIL_PASSWORD');
Config::write('em.secure', true);
