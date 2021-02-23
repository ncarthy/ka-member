<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
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
include_once '../objects/member.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare member object
$member = new Member($db);

// get member id
$data = json_decode(file_get_contents("php://input"));

// set member id to be deleted
$member->id = !empty($data->id) ? $data->id : die();

// read the details of$member to be edited
$member->readOne();

if (empty($member->username) ) {
    http_response_code(422);   
    echo json_encode(
        array("message" => "No Member found with id = " . $member->id)
    );
    exit(1);
}

// delete the member
if($member->delete()){
    echo '{';
        echo '"message": "The member was removed from the system."';
    echo '}';
}

// if unable to delete the member
else{
    echo '{';
        echo '"message": "Unable to delete member."';
    echo '}';
}
?>