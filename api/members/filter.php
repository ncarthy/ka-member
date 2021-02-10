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
include_once '../objects/member_filter.php';

// instantiate database and member object
$database = new Database();
$db = $database->getConnection();

// initialize object
$filter = new MemberFilter($db);
$filter->reset();

// get posted data
$data = json_decode(file_get_contents("php://input"));

if(isset($data->surname)) {
    $filter->setSurname($data->surname);
}
if(isset($data->businessname)) {
    $filter->setBusinessname($data->businessname);
}
if(isset($data->membertypeid)) {
    $filter->setMemberTypeID($data->membertypeid);
}

// The default is to only view the un-deleted members
// So if removed is set to 0 or is missing then only
// non-deleted members will appear in the list
// if removed is set to 'amy' then no filter applied
if (isset($data->removed)) {
    if ($data->removed =='any') {    
        // no filter applied
    } else {
        if ($data->removed) {
            $filter->setDeleted();
        } else {
            $filter->setNotDeleted();
        }
    }
} else {
    $filter->setNotDeleted();
}

$stmt=$filter->execute();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    $members_arr=array();
    $members_arr["count"] = $num; // add the count of rows
    $members_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
        $members_item=array(
            "id" => $idmember,
            "idmembership" => $idmembership,
            "type" => $membershiptype,
            "name" => $name,
            "business" => $businessname,
            "note" => $note,
            "addressfirstline" => $addressfirstline,
            "addresssecondline" => $addresssecondline,
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country,
            "gdpr_email" => $gdpr_email,
            "gdpr_tel" => $gdpr_tel,
            "gdpr_address" => $gdpr_address,
            "gdpr_sm" => $gdpr_sm,
            "expirydate" => $expirydate,
            "joindate" => $joindate,
            "reminderdate" => $reminderdate,
            "updatedate" => $updatedate,
            "deletedate" => $deletedate,
            "paymentmethod" => $paymentmethod,
            "lasttransactiondate" => $lasttransactiondate
        );

        // create un-keyed list
        array_push ($members_arr["records"], $members_item);
    }    

    echo json_encode($members_arr, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
}
?>