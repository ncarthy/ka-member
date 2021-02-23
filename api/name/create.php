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
include_once '../objects/member_name.php';

$db = Database::getInstance()->conn;
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$new_item = new MemberName($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set new_item property values
$new_item->honorific = $data->honorific;
$new_item->firstname = $data->firstname;
$new_item->surname = $data->surname;
$new_item->idmember = $data->idmember;

// INSERT the row into the database
try{
    $return_value = $new_item->create();
} catch (PDOException $e) {
    $num = $e->getCode();
    $code = $e->errorInfo[1];
    if ($code == 1062) {
        // Take some action if there is a key constraint violation, i.e. duplicate name
        echo '{';
            echo '"message": "Unable to INSERT row: duplicate name for that member."';
        echo '}';
        return;
    } else {
        throw $e;
    }
}

if($return_value) {
    echo '{';
        echo '"message": "New name with idmembername=' . $new_item->id . ' was created.",';
        echo '"idmembername":' . $new_item->id;
    echo '}';
} else {
echo '{';
    echo '"message": "Unable to INSERT row."';
echo '}';
}

?>