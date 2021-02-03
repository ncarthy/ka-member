<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

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
include_once '../objects/membership_status.php';

// instantiate database and status object
$database = new Database();
$db = $database->getConnection();

// initialize object
$status = new MembershipStatus($db);

// query shops
$stmt = $status->read();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $membership_status_arr=array();
    $membership_status_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
            $status_item=array(
                "id" => $id,
                "name" => $name
            );

            // create associative array keyed on status id
            $membership_status_arr["records"][$id] = $status_item;
        }

        echo json_encode($membership_status_arr);
}
?>