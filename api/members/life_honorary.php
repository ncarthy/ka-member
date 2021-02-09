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
include_once '../objects/members.php';

// instantiate database and member object
$database = new Database();
$db = $database->getConnection();

// initialize object
$member = new Members($db);

// query 
$stmt = $member->lifeAndHonorary();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    $members_arr=array();
    $members_arr["records"]=array();

    $honorary_count =0; // count of hon members

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
            "type" => $membershiptype,
            "name" => $name,
            "business" => $businessname,
            "note" => $note,
            "address1" => $address1,
            "address2" => $address2,
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country,
            "still_paying" => $count > 0 ? true : false
        );

        if ($idmembership == 6) {
            $honorary_count++;
        }

        // create un-keyed list
        array_push ($members_arr["records"], $members_item);
    }

    $members_arr["honorary"] = $honorary_count; // add the count of hon members
    $members_arr["lifetime"] = $num - $honorary_count; // add the count of lifetime members
    $members_arr["total"] = $num; // add the count of lifetime members


    echo json_encode($members_arr, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
}
?>