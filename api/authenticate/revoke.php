<?php

$jwt = new \Models\JWTWrapper();

if(!$jwt->loggedIn){      
    exit(0);
} else if ($jwt->id) {
    $usertoken = new \Models\UserToken();
    $usertoken->deleteAll($jwt->id);

    setcookie(\Core\Config::read('token.cookiename'), '', time() - 3600);
}

?>