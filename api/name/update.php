<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
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
include_once '../objects/member_name.php';

// get database connection
$db = Database::getInstance()->conn;
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

// prepare object to be updated
$item = new MemberName($db);

// get id of object to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of row to be edited
$item->id = $data->id;

// set property values
$item->honorific = $data->honorific;
$item->firstname = $data->firstname;
$item->surname = $data->surname;
$item->idmember = $data->idmember;

// UPDATE the row in the database
try{
    $return_value = $item->update();
} catch (PDOException $e) {
    $num = $e->getCode();
    $code = $e->errorInfo[1];
    if ($code == 1062) {
        // Take some action if there is a key constraint violation, i.e. duplicate name
        echo '{';
            echo '"message": "Unable to UPDATE name: that would create a duplicate name for the member."';
        echo '}';
        return;
    } else {
        throw $e;
    }
}

if($return_value) {
    echo '{';
        echo '"message": "Name updated."';
    echo '}';
} else {
echo '{';
    echo '"message": "Unable to UPDATE row."';
echo '}';
}
?>