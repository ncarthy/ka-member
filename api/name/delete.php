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
include_once '../objects/member_name.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare member_name object
$member_name = new MemberName($db);

// get member_name id
$data = json_decode(file_get_contents("php://input"));

// set member_name id to be deleted
if (empty($data->idmembername)) {
    http_response_code(422); 
    echo json_encode(
        array("message" => "Please supply the idmembername in the body of the POST request.")
    );
    exit(1);
}
$member_name->id = !empty($data->idmembername) ? $data->idmembername : die();

// delete the member_name
if($member_name->delete()){
    echo '{';
        echo '"message": "The member name was removed from the system."';
    echo '}';
}

// if unable to delete the member_name
else{
    echo '{';
        echo '"message": "Unable to delete member name."';
    echo '}';
}
?>