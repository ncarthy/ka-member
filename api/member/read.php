<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

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
include_once '../objects/member.php';

// instantiate database and member object
$db = Database::getInstance()->conn;

// initialize object
$member = new Member($db);

// query database, return with dataset
$stmt = $member->readAll();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $member_arr=array();
    $member_arr["count"] = $num;
    $member_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
        $member_item=array(
            "id" => $id,
            "title" => $title,
            "businessname" => html_entity_decode($businessname),
            "bankpayerref" => html_entity_decode($bankpayerref),
            "note" => html_entity_decode($note),
            "addressfirstline" => html_entity_decode($addressfirstline),
            "addresssecondline" => html_entity_decode($addresssecondline),
            "city" => html_entity_decode($city),
            "county" => html_entity_decode($county),
            "postcode" => html_entity_decode($postcode),
            "countryID" => $countryID,
            "area" => html_entity_decode($area),
            "email1" => html_entity_decode($email1),
            "phone1" => html_entity_decode('xn#'.$phone1),
            "addressfirstline2" => html_entity_decode($addressfirstline2),
            "addresssecondline2" => html_entity_decode($addresssecondline2),
            "city2" => html_entity_decode($city2),
            "county2" => html_entity_decode($county2),
            "postcode2" => html_entity_decode($postcode2),
            "country2ID" => $country2ID,
            "email2" => html_entity_decode($email2),
            "phone2" => html_entity_decode('xn#'.$phone2),
            "statusID" => $statusID,
            "expirydate" => $expirydate,
            "joindate" => $joindate,
            "reminderdate" => $reminderdate,
            "updatedate" => $updatedate,
            "deletedate" => $deletedate,
            "repeatpayment" => $repeatpayment,
            "recurringpayment" => $recurringpayment,
            "username" => $username,
            "gdpr_email" => $gdpr_email,
            "gdpr_tel" => $gdpr_tel,
            "gdpr_address" => $gdpr_address,
            "gdpr_sm" => $gdpr_sm,
            "postonhold" => $postonhold
        );

        // create associative array keyed on id
        array_push($member_arr["records"], $member_item);

    }    

    $encoded_json = json_encode($member_arr, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
    echo str_replace('xn#','',$encoded_json);
    file_put_contents('php://stderr', print_r($member_item, TRUE));
}
?>