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

$member = new Member($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set member property values
$member->title = $data->title;
$member->businessname = $data->businessname;
$member->bankpayerref = $data->bankpayerref;
$member->note = $data->note;
$member->addressfirstline = $data->addressfirstline;
$member->addresssecondline = $data->addresssecondline;
$member->city = $data->city;
$member->county = $data->county;
$member->postcode = $data->postcode;
$member->country = $data->country;
$member->area = $data->area;
$member->email1 = $data->email1;
$member->phone1 = $data->phone1;
$member->addressfirstline2 = $data->addressfirstline2;
$member->addresssecondline2 = $data->addresssecondline2;
$member->city2 = $data->city2;
$member->county2 = $data->county2;
$member->postcode2 = $data->postcode2;
$member->country2 = $data->country2;
$member->email2 = $data->email2;
$member->phone2 = $data->phone2;
$member->statusID = $data->statusID;
$member->expirydate = $data->expirydate;
$member->joindate = $data->joindate;
$member->updatedate = $data->updatedate;
$member->deletedate = $data->deletedate;
$member->repeatpayment = $data->repeatpayment;
$member->recurringpayment = $data->recurringpayment;
$member->username = $data->username;
$member->gdpr_email = $data->gdpr_email;
$member->gdpr_tel = $data->gdpr_tel;
$member->gdpr_address = $data->gdpr_address;
$member->gdpr_sm = $data->gdpr_sm;

// create the member
if($member->create()){
    echo '{';
        echo '"message": "New member was created."';
    echo '}';
}

// if unable to create the member, tell the admin
else{
    echo '{';
        echo '"message": "Unable to create new member."';
    echo '}';
}
?>