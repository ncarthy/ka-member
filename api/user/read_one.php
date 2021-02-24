<?php
include_once '../config/core.php';
header("Access-Control-Allow-Origin: ". Config::read('server'));
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");

if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

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
include_once '../objects/user.php';

// get database connection
$db = Database::getInstance()->conn;

// prepare user object
$user = new User($db);

// set ID property of user to be edited
$user->id = isset($_GET['id']) ? $_GET['id'] : die();

// Allow to progress if isadmin is true OR logged in user is searching own profile
if (!$jwt->isAdmin && $jwt->id != $user->id){
    http_response_code(401);  
    echo json_encode(
        array("message" => "Must be an admin.")
    );
    exit(1);
}

// read the details of user to be edited
$user->readOne();

if (empty($user->username) ) {
    http_response_code(422);   
    echo json_encode(
        array("message" => "No User found with id = " . $user->id)
    );
    exit(1);
}

// create array
$user_arr = array(
    "id" => $user->id,
    "username" => $user->username,
    "fullname" => html_entity_decode($user->fullname),
    "role" => $user->role,
    "isadmin" => $user->role=='Admin' ? true : false,
    "suspended" => $user->suspended
);

// make it json format
print_r(json_encode($user_arr, JSON_NUMERIC_CHECK));
?>