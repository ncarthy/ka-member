<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

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

// instantiate database and user object
$database = new Database();
$db = $database->getConnection();

// initialize object
$user = new Member($db);

// query shops
$stmt = $user->readAll();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $member_arr=array();
    $member_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
            $user_item=array(
                "id" => $id,
                "title" => $title,
                "businessname" => html_entity_decode($businessname),
                "bankpayerref" => html_entity_decode($bankpayerref),
                "note" => html_entity_decode($note),
                "addressfirstline" => html_entity_decode($addressfirstline),
                "addresssecondline" => html_entity_decode($addresssecondline),
                "city" => html_entity_decode($city),
                "county" => html_entity_decode($county),
                "country" => html_entity_decode($country),
                "email" => html_entity_decode($email1),
                "phone" => html_entity_decode($phone1),
                "addressfirstline2" => html_entity_decode($addressfirstline2),
                "addresssecondline2" => html_entity_decode($addresssecondline2),
                "city2" => html_entity_decode($city2),
                "county2" => html_entity_decode($county2),
                "country2" => html_entity_decode($country2),
                "email2" => html_entity_decode($email2),
                "phone2" => html_entity_decode($phone2),
                "statusID" => $statusID,
                "expirydate" => $expirydate,
                "joindate" => $joindate,
                "updatedate" => $updatedate,
                "deletedate" => $deletedate,
                "repeatpayment" => $repeatpayment,
                "recurringpayment" => $recurringpayment,
                "username" => $username,
                "gdpr_email" => $gdpr_email,
                "gdpr_tel" => $gdpr_tel,
                "gdpr_address" => $gdpr_address,
                "gdpr_sm" => $gdpr_sm
            );

            // create associative array keyed on username
            $users_arr["records"][$id] = $user_item;
        }

        echo json_encode($users_arr);
}
?>