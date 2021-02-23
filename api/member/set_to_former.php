<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
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
include_once '../objects/member.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare object to be updated
$item = new Member($db);

// get id of item to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of user to be edited
$item->id = $data->id;
$item->updatedate = null; // will insert current_timestamp
$item->username = $jwt->user; // JWT user

// UPDATE the row in the database
if($item->setToFormerMember()){
    echo '{';
        echo '"message": "Member with id=' . $item->id . ' set to former member.",';
        echo '"id":' . $item->id;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    echo '{';
        echo '"message": "Unable to set member to former.",';
        echo '"id":' . $item->id;
    echo '}';
}
?>