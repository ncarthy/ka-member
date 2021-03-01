<?php

namespace Core;

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