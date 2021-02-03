<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

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

// include database and object files
include_once '../config/database.php';
include_once '../objects/membership_status.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare takings object
$status = new MembershipStatus($db);

// set ID property of takings to be edited
if (isset($_GET['id'])) {
    $status->id = $_GET['id'];
}
else if (isset($_GET['name'])) {
    $status->name = $_GET['name'];
}
else 
{
    http_response_code(422); 
    echo json_encode(
        array("message" => "Please supply either id or name as a parameter.")
    );
    exit(1);
}

// read the details of takings to be edited
$status->readOne();

// create array
$status_arr = array(
    "id" => $status->id,
    "name" => $status->name    
);

// make it json format
echo json_encode($status_arr);
?>