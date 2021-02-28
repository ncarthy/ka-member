<?php

// instantiate JWT and user object
$jwt = new \Models\JWTWrapper();
$user = new \Models\User();
$user->id = $jwt->checkRefreshToken();

if ($user->id) {

    // read the details of user to be edited
    $user->readOne();

    if (empty($user->username) ) {
        http_response_code(422);   
        echo json_encode(
            array("message" => "No User found with id = " . $user->id)
        );
        exit(1);
    }

    $accessToken = $jwt->getAccessToken($user->id,$user->username,$user->role);

    $user_with_token=array(
        "username" => $user->username,
        "id" => $user->id,
        "role" => $user->role, 
        "fullname" => $user->fullname,
        "accessToken" => (string)$accessToken
    );

    echo json_encode($user_with_token);
}
else {
    http_response_code(401); 
    echo json_encode(
        array("message" => "Unauthorized")
    );
}

?>