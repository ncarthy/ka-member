<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Access-Control-Allow-Headers, Authorization");

if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

// include database and object files
include_once '../config/database.php';
include_once '../objects/usertoken.php';
include_once '../objects/jwt.php';

$jwt = new JWTWrapper();

if(!$jwt->loggedIn){      
    exit(0);
} else if ($jwt->id) {
    $db = Database::getInstance()->conn;
    $usertoken = new UserToken($db);
    $usertoken->deleteAll($jwt->id);

    setcookie('refreshToken', '', time() - 3600);
}

?>