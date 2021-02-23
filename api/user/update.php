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
include_once '../objects/user.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare object to be updated
$item = new User($db);

// get id of object to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of row to be edited
$item->id = $data->id;

// set property values
$item->username = $data->username;
$item->fullname = $data->fullname;
$item->isadmin = $data->isadmin;
$item->role = $data->isadmin?'Admin':'User';
$item->suspended = $data->suspended;
if (isset($data->password)) {
    $item->password = password_hash($data->password, PASSWORD_DEFAULT);
    $item->checkPassword($data->password, $errors);
    if ($errors) {
        http_response_code(422);  
        echo '{';
            echo '"message": "'.implode(" & ",$errors).'"';
        echo '}';
    } 
    exit(1);
}

// UPDATE the row in the database
if($item->update()){
    echo '{';
        echo '"message": "User with id=' . $item->id . ' was updated.",';
        echo '"id":' . $item->id;
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    http_response_code(422);
    echo '{';
        echo '"message": "Unable to UPDATE row.",';
        echo '"id":' . $item->id;
    echo '}';
}
?>