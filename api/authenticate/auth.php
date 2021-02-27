<?php

header("Access-Control-Allow-Credentials: true");

// instantiate database and user object
$db = \Core\Database::getInstance()->conn;
$user = new \Models\User($db);
$usertoken = new \Models\UserToken($db);
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
        $jwt = new \Models\JWTWrapper();
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