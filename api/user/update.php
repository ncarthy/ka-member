<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

// get id of user to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of user to be edited
$user->id = $data->id;

// set user property values
$user->username = $data->username;
$user->fullname = $data->fullname;
$user->isadmin = $data->isadmin;
$user->suspended = $data->suspended;
if (isset($data->password)) {
    $user->password = password_hash($data->password, PASSWORD_DEFAULT);
}

// update the takings
if($user->update()){
    echo '{';
        echo '"message": "The user was updated."';
    echo '}';
}

// if unable to update the user, tell the user
else{
    echo '{';
        echo '"message": "Unable to update user info."';
    echo '}';
}
?>