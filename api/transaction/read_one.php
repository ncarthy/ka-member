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
include_once '../objects/transaction.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare user object
$item = new Transaction($db);

// set ID property of transaction
$item->id = isset($_GET['id']) ? $_GET['id'] : die();

// read the details of transaction to be edited
$item->readOne();

if (empty($item->idmember) ) {
    http_response_code(422);   
    echo json_encode(
        array("message" => "No Transaction found with idtransaction = " . $item->id)
    );
    exit(1);
}

// create array
$item_arr = array(
    "id" => $item->id,
    "date" => $item->date,
    "paymentmethod" => html_entity_decode($item->paymentmethod),
    "amount" => $item->amount,
    "idmember" => $item->idmember,
    "bankID" => $item->bankID
);

// make it json format
print_r(json_encode($item_arr, JSON_NUMERIC_CHECK));
?>