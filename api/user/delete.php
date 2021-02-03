<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Check logged in
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

// include database and object file
include_once '../config/database.php';
include_once '../objects/user.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare user object
$user = new User($db);

// get user id
$data = json_decode(file_get_contents("php://input"));

// set user id to be deleted
$user->id = !empty($data->id) ? $data->id : die();

// delete the user
if($user->delete()){
    echo '{';
        echo '"message": "The user was removed from the system."';
    echo '}';
}

// if unable to delete the user
else{
    echo '{';
        echo '"message": "Unable to delete user."';
    echo '}';
}
?>