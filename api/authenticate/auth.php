<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
include_once '../objects/jwt.php';

// instantiate database and user object
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$usernm = '';
$pass = '';

// time limit on JWT session tokens
$expirationLimit = '+1 month';

// get posted data
$data = json_decode(file_get_contents("php://input"));
// set property values
if (isset($data)) {
    $usernm = isset($data->username) ? $data->username : '';
    $pass = isset($data->password) ? $data->password : '';
}

// Un-comment these 2 lines for testing via GET
//$usernm = isset($_GET["username"]) ? $_GET["username"] : '';
//$pass = isset($_GET["password"]) ? $_GET["password"] : '';

// query takings
$stmt = $user->readOneRaw(strtolower($usernm));
$num = $stmt->rowCount();

// check if more than 0 records found
if($num>0){
 
    // products array
    $takings_arr=array();
    $takings_arr["records"]=array();

    // take just the first row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // extract row
    // this will make $row['name'] to
    // just $name only
    extract($row);

    if ($suspended) {
        http_response_code(401);
        echo json_encode(
            array("message" => "User is suspended.")
        );
    }
    else if (password_verify($pass, $password)){
        
        // Create a new JWT, with claims of username and isAdmin        
        $jwt = new JWTWrapper();
        $now = new DateTimeImmutable();
        $expiry = $now->modify('+1 hour');
        $token = $jwt->getToken($username, $isAdmin, $now, $expiry);

        $user_with_token=array(
            "username" => $username,
            "id" => $id,
            "isAdmin" => $isAdmin,
            "expiry" => $expiry->format("Y-m-d H:i:s"),
            "token" => (string)$token
        );

        // echo json_encode($user_with_token, JSON_PRETTY_PRINT);
        echo json_encode($user_with_token);
    }
    else if (empty($pass)) {
        http_response_code(401);
        echo json_encode(
            array("message" => "Please supply a password.")
        );
    }
    else{
        http_response_code(401);
        echo json_encode(
            array("message" => "Unable to validate that username and password.")
        );
    }

}
else{
        http_response_code(401);
        echo json_encode(
            array("message" => "Unable to validate that username and password.")
        );
    }
?>