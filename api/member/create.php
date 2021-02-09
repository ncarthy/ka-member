<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
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
include_once '../objects/member.php';

$database = new Database();
$db = $database->getConnection();

$new_item = new Member($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set new_item property values
$new_item->title = $data->title;
$new_item->businessname = $data->businessname;
$new_item->bankpayerref = $data->bankpayerref;
$new_item->note = $data->note;
$new_item->addressfirstline = $data->addressfirstline;
$new_item->addresssecondline = $data->addresssecondline;
$new_item->city = $data->city;
$new_item->county = $data->county;
$new_item->postcode = $data->postcode;
$new_item->country = $data->country;
$new_item->area = $data->area;
$new_item->email1 = $data->email1;
$new_item->phone1 = $data->phone1;
$new_item->addressfirstline2 = $data->addressfirstline2;
$new_item->addresssecondline2 = $data->addresssecondline2;
$new_item->city2 = $data->city2;
$new_item->county2 = $data->county2;
$new_item->postcode2 = $data->postcode2;
$new_item->country2 = $data->country2;
$new_item->email2 = $data->email2;
$new_item->phone2 = $data->phone2;
$new_item->statusID = $data->statusID;
$new_item->expirydate = $data->expirydate;
$new_item->joindate = $data->joindate;
$new_item->updatedate = $data->updatedate;
$new_item->reminderdate = $data->reminderdate;
$new_item->deletedate = $data->deletedate;
$new_item->repeatpayment = $data->repeatpayment;
$new_item->recurringpayment = $data->recurringpayment;
$new_item->username = $data->username;
$new_item->gdpr_email = $data->gdpr_email;
$new_item->gdpr_tel = $data->gdpr_tel;
$new_item->gdpr_address = $data->gdpr_address;
$new_item->gdpr_sm = $data->gdpr_sm;

// INSERT the row into the database
if($new_item->create()){
    echo '{';
        echo '"message": "New member with id=' . $new_item->id . ' was created.",';
        echo '"id":' . $new_item->id;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    echo '{';
        echo '"message": "Unable to INSERT row."';
    echo '}';
}
?>