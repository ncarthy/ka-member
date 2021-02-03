<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Check logged in status
include_once '../objects/jwt.php';
$jwt = new JWTWrapper();
if(!$jwt->loggedIn){      
    http_response_code(401);  
    echo json_encode(
        array("message" => "Not logged in.")
    );
    exit(1);
}
else if (!$jwt->isAdmin){
    http_response_code(401);  
    echo json_encode(
        array("message" => "Must be an admin.")
    );
    exit(1);
}

// get database connection
include_once '../config/database.php';
include_once '../objects/user.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set user property values
$user->username = $data->username;
$user->isadmin = $data->isadmin;
$user->suspended = $data->suspended;
$user->fullname = $data->fullname;
$user->password = password_hash($data->password, PASSWORD_DEFAULT);

// create the user
if($user->create()){
    echo '{';
        echo '"message": "New user was created."';
    echo '}';
}

// if unable to create the user, tell the admin
else{
    echo '{';
        echo '"message": "Unable to create new user."';
    echo '}';
}
?>