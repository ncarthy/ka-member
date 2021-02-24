<?php
include_once '../config/core.php';
header("Access-Control-Allow-Origin: ". Config::read('server'));
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Origin, Content-Type, Access-Control-Allow-Headers, Authorization");

if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
include_once '../objects/usertoken.php';
include_once '../objects/jwt.php';

// instantiate database and user object
$db = Database::getInstance()->conn;
$user = new User($db);
$usertoken = new UserToken($db);
$usernm = '';
$pass = '';
$numberPasswordAttempts = 5;

// get posted data
$data = json_decode(file_get_contents("php://input"));
// set property values
if (isset($data)) {
    $usernm = isset($data->username) ? $data->username : '';
    $pass = isset($data->password) ? $data->password : '';
}

// query database for a user with that username
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

    if ($suspended || $failedloginattempts > 4) {
        http_response_code(401);
        echo json_encode(
            array("message" => "User is suspended.")
        );
    }
    else if (password_verify($pass, $password)){
        
        // Create a new access and refresh JWT pair, with claims of username and isAdmin  
        // Suspended is not a claim because you can't get to this point if user is suspended
        $jwt = new JWTWrapper();
        $accessToken = $jwt->getAccessToken($id,$username,$isAdmin ? 'Admin' : 'User');

        $user_with_token=array(
            "username" => $username,
            "id" => $id,
            "role" => $isAdmin ? 'Admin' : 'User', 
            "fullname" => $name,
            //"expiry" => $accessTokenExpiry->format("Y-m-d H:i:s"), // For debugging purposes
            "accessToken" => (string)$accessToken
        );

        $user->updateFailedAttempts($id, 0, false);

        // echo json_encode($user_with_token, JSON_PRETTY_PRINT);
        echo json_encode($user_with_token);
    }
    else{
        $failedloginattempts++;

        $user->updateFailedAttempts($id, $failedloginattempts, ($failedloginattempts==$numberPasswordAttempts));

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