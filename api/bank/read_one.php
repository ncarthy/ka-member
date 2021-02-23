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
include_once '../objects/bank_account.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare takings object
$item = new BankAccount($db);

// set ID property of takings to be edited
if (isset($_GET['id'])) {
    $item->id = $_GET['id'];
}
else if (isset($_GET['name'])) {
    $item->name = $_GET['name'];
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
$item->readOne();

// create array
$items_arr = array(
    "id" => $item->id,
    "name" => $item->name    
);

// make it json format
echo json_encode($items_arr, JSON_NUMERIC_CHECK);
?>