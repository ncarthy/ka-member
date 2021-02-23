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

// instantiate database and user object
$db = Database::getInstance()->conn;

// initialize object
$user = new User($db);

if (isset($_GET['suspended']) ) {
    $user->suspended = $_GET['suspended'];
}

// query database, return with dataset
$stmt = $user->readAll();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $users_arr=array();
    $users_arr["count"]=$num;
    $users_arr["records"]=array();

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
                "username" => $username,
                "fullname" => html_entity_decode($name),
                "isadmin" => $isAdmin,
                "role" => $isAdmin?'Admin':'User',
                "suspended" => $suspended
            );

            // create associative array keyed on username
            $users_arr["records"][$username] = $user_item;
        }

        echo json_encode($users_arr, JSON_NUMERIC_CHECK);
}
?>