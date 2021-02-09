<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
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
$database = new Database();
$db = $database->getConnection();

// prepare object to be updated
$item = new Member($db);

// get id of item to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of user to be edited
$item->id = $data->id;

// set item property values
$item->title = $data->title;
$item->businessname = $data->businessname;
$item->bankpayerref = $data->bankpayerref;
$item->note = $data->note;
$item->addressfirstline = $data->addressfirstline;
$item->addresssecondline = $data->addresssecondline;
$item->city = $data->city;
$item->county = $data->county;
$item->postcode = $data->postcode;
$item->countryID = $data->countryID;
$item->area = $data->area;
$item->email1 = $data->email1;
$item->phone1 = $data->phone1;
$item->addressfirstline2 = $data->addressfirstline2;
$item->addresssecondline2 = $data->addresssecondline2;
$item->city2 = $data->city2;
$item->county2 = $data->county2;
$item->postcode2 = $data->postcode2;
$item->country2ID = $data->country2ID;
$item->email2 = $data->email2;
$item->phone2 = $data->phone2;
$item->statusID = $data->statusID;
$item->expirydate = $data->expirydate;
$item->joindate = $data->joindate;
$item->reminderdate = $data->reminderdate;
$item->updatedate = null;
$item->deletedate = $data->deletedate;
$item->repeatpayment = $data->repeatpayment;
$item->recurringpayment = $data->recurringpayment;
$item->username = $jwt->user; // JWT user
$item->gdpr_email = $data->gdpr_email;
$item->gdpr_tel = $data->gdpr_tel;
$item->gdpr_address = $data->gdpr_address;
$item->gdpr_sm = $data->gdpr_sm;
$item->postonhold = $data->postonhold;

// UPDATE the row in the database
if($item->update()){
    echo '{';
        echo '"message": "Member with id=' . $item->id . ' was updated.",';
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