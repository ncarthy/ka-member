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
include_once '../objects/member_name.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare takings object
$item = new MemberName($db);

// set ID property of name to be edited
if (isset($_GET['idmembername'])) {
    $item->id = $_GET['idmembername'];
} else {
    http_response_code(422); 
    echo json_encode(
        array("message" => "Please supply the idmembername as a parameter.")
    );
    exit(1);
}

// read the details of takings to be edited
$item->readOne();

// create array
$items_arr = array(
    "id" => $item->id,
    "honorific" => $item->honorific,
    "firstname" => $item->firstname,
    "surname" => $item->surname,
    "idmember" => $item->idmember
);

if (!$item->surname) {
    http_response_code(422); 
    echo json_encode(
        array("message" => "No names found with that idmembername.",
                "idmembername" => $item->id), JSON_NUMERIC_CHECK
    );
    exit(1);
}

// make it json format
echo json_encode($items_arr, JSON_NUMERIC_CHECK);
?>