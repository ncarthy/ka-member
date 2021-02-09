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

// include database and object files
include_once '../config/database.php';
include_once '../objects/member.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare$member object
$member = new Member($db);

// set ID property of$member to be edited
$member->id = isset($_GET['id']) ? $_GET['id'] : die();

// read the details of$member to be edited
$member->readOne();

if (empty($member->username) ) {
    http_response_code(422);   
    echo json_encode(
        array("message" => "No Member found with id = " . $member->id)
    );
    exit(1);
}

// create array
$member_item=array(
    "id" => $member->id,
    "title" => $member->title,
    "businessname" => $member->businessname,
    "bankpayerref" => $member->bankpayerref,
    "note" => $member->note,
    "addressfirstline" => $member->addressfirstline,
    "addresssecondline" => $member->addresssecondline,
    "city" => $member->city,
    "county" => $member->county,
    "postcode" => $member->postcode,
    "country" => $member->country,
    "area" => $member->area,
    "email1" => $member->email1,
    "phone1" => 'xn#'.$member->phone1,
    "addressfirstline2" => $member->addressfirstline2,
    "addresssecondline2" => $member->addresssecondline2,
    "city2" => $member->city2,
    "county2" => $member->county2,
    "postcode2" => $member->postcode2,
    "country2" => $member->country2,
    "email2" => $member->email2,
    "phone2" => 'xn#'.$member->phone2,
    "statusID" => $member->statusID,
    "expirydate" => $member->expirydate,
    "joindate" => $member->joindate,
    "reminderdate" => $member->reminderdate,
    "updatedate" => $member->updatedate,
    "deletedate" => $member->deletedate,
    "repeatpayment" => $member->repeatpayment,
    "recurringpayment" => $member->recurringpayment,
    "username" => $member->username,
    "gdpr_email" => $member->gdpr_email,
    "gdpr_tel" => $member->gdpr_tel,
    "gdpr_address" => $member->gdpr_address,
    "gdpr_sm" => $member->gdpr_sm,    
);

// make it json format
$encoded_json = json_encode($member_item, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);

print_r(str_replace('xn#','',$encoded_json));
?>