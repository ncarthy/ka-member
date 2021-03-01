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
Config::write('db.host', '192.168.1.22');
Config::write('db.port', '3306');
Config::write('db.name', 'knightsb_membership');
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

class Headers
{
    public static function getHeaders($path_is_auth = false) {
        if ($path_is_auth || Headers::path_is_auth()) {
            Headers::cors_headers();
        } else {
            Headers::normal_headers();
        }
    }

    public static function path_is_auth()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $api_prefix = \Core\Config::read('api.path');
    
        if (substr($path, 0, strlen($api_prefix)) == $api_prefix) {
            $path = substr($path, strlen($api_prefix));
        } 

        if (  (strlen($path) < 4) || (substr($path, 0, 4) != "auth") ) {
            return false;
        } else {
            return true;
        }
    }

    private static function cors_headers()
    {
        header("Access-Control-Allow-Origin: ". \Core\Config::read('server'));
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

    private static function normal_headers()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

}

?>