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
include_once '../objects/transaction.php';

$db = Database::getInstance()->conn;

$new_item = new Transaction($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set new_item property values
$new_item->date = $data->date;
$new_item->amount = $data->amount;
$new_item->paymentmethod = $data->paymentmethod;
$new_item->idmember = $data->idmember;
$new_item->bankID = $data->bankID;

// INSERT the row into the database
if($new_item->create()){
    echo '{';
        echo '"message": "New transaction with id=' . $new_item->id . ' was created.",';
        echo '"id":' . $new_item->id.',';
        echo '"idmember":' . $new_item->idmember;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    echo '{';
        echo '"message": "Unable to INSERT row."';
    echo '}';
}
?>