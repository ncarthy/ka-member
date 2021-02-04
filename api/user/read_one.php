<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

//Check logged in
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

// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare user object
$user = new User($db);

// set ID property of user to be edited
$user->id = isset($_GET['id']) ? $_GET['id'] : die();

// read the details of user to be edited
$user->readOne();

if (empty($user->username) ) {
    http_response_code(422);   
    echo json_encode(
        array("message" => "No User found with id = " . $user->id)
    );
    exit(1);
}

// create array
$user_arr = array(
    "id" => $user->id,
    "username" => $user->username,
    "fullname" => html_entity_decode($user->fullname),
    "isadmin" => $user->isadmin,
    "suspended" => $user->suspended
);

// make it json format
print_r(json_encode($user_arr, JSON_NUMERIC_CHECK));
?>