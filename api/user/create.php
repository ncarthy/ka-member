<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
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

$db = Database::getInstance()->conn;

$new_item = new User($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set new_item property values
$new_item->username = $data->username;
$new_item->isadmin = $data->isadmin;
$new_item->suspended = $data->suspended;
$new_item->fullname = $data->fullname;
$new_item->failedloginattempts = $data->failedloginattempts;
$new_item->password = password_hash($data->password, PASSWORD_DEFAULT);

$new_item->checkPassword($data->password, $errors);
if ($errors) {
    http_response_code(422);  
    echo '{';
        echo '"message": "'.implode(" & ",$errors).'"';
    echo '}';
} 

else if($new_item->create()) {
    echo '{';
        echo '"message": "New user with id=' . $new_item->id . ' was created.",';
        echo '"id":' . $new_item->id;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    http_response_code(422);  
    echo '{';
        echo '"message": "Unable to INSERT row."';
    echo '}';
}
?>