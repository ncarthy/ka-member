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
include_once '../objects/transaction.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare object to be updated
$item = new Transaction($db);

// get id of object to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of row to be edited
$item->id = $data->id;

// set property values
$item->date = $data->date;
$item->amount = $data->amount;
$item->paymentmethod = $data->paymentmethod;
$item->idmember = $data->idmember;
$item->bankID = $data->bankID;

// UPDATE the row in the database
if($item->update()){
    echo '{';
        echo '"message": "Transaction with id=' . $item->id . ' was updated.",';
        echo '"id":' . $item->id;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    echo '{';
        echo '"message": "Unable to UPDATE row.",';
        echo '"id":' . $item->id;
    echo '}';
}
?>