<?php

namespace Core;

use \PDO;

class Database{
    public $conn;

    // Singleton pattern from https://stackoverflow.com/a/2047999/6941165
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    private function __construct(){

        $this->conn = null;

        try{
            $host = Config::read('db.host');
            $port = Config::read('db.port');

            if ($this->testConnection($host, $port)) {

                $this->conn = new PDO("mysql:host=" . $host . ";port=" . 
                                            $port. ";dbname=" . 
                                            Config::read('db.name') . ";charset=utf8"
                                            , getenv(Config::read('db.user'))
                                            , getenv(Config::read('db.password'))
                                        );

                // From https://stackoverflow.com/a/60496/6941165
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
            }
            else {
                http_response_code(503);
                echo json_encode(
                    array("message" => "Connection error: " . "Connection refused by " .$host . " on ". $port)
                );
                exit(1);
            }
                
        }catch(\PDOException $exception){
            http_response_code(500);
            echo "Connection error: " . $exception->getMessage();
            echo " Unable to connect to MariaDB database. Check configuration: database name, username and password.";
            exit(1);
        }
    }

    private function testConnection($host, $port){
        $waitTimeoutInSeconds = 1;
        if ($fp = fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)) {
            // It worked
            return true;
        } else {
            // It didn't work
            return false;
        }
        fclose($fp);
    }
}