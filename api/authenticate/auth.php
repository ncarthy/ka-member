<?php
include_once '../config/core.php';
header("Access-Control-Allow-Origin: ".$ORIGIN);
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");

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
$numberPasswordAttempts = 5;

// time limit on JWT session tokens
$accessTokenExpirationLimit = '+15 minute';
$refreshTokenExpirationLimit = '+7 day';

// get posted data
$data = json_decode(file_get_contents("php://input"));
// set property values
if (isset($data)) {
    $usernm = isset($data->username) ? $data->username : '';
    $pass = isset($data->password) ? $data->password : '';
} else {
    // Maybe a preflight OPTIONS request. Just exit
    exit(0);
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
        
        // Create a new JWT, with claims of username and isAdmin  
        // Suspended is not a claim because you can't get to this point if user is suspended
        $jwt = new JWTWrapper();
        $now = new DateTimeImmutable();
        $accessTokenExpiry = $now->modify($accessTokenExpirationLimit);
        $accessToken = $jwt->getToken($username, $isAdmin, $now, $accessTokenExpiry);
        $refreshTokenExpiry = $now->modify($refreshTokenExpirationLimit);
        $refreshToken = $jwt->getToken($username, $isAdmin, $now, $refreshTokenExpiry);

        $user_with_token=array(
            "username" => $username,
            "id" => $id,
            "role" => $isAdmin ? 'Admin' : 'User', 
            "fullname" => $name,
            "expiry" => $accessTokenExpiry->format("Y-m-d H:i:s"), // For debugging purposes
            "jwtToken" => (string)$accessToken
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