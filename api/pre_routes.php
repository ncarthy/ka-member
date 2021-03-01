<?php

/*
    'Pre' Router logic allied in this file:

    * Only logged in users can access the api, except for requests to 'auth'.
      The exception is to allow the presenting of credentials and return of tokens.

    * If it is an 'auth' request then set 'Allow-Credentials' header

    * Commands that affect data on the server (PUT/POST/DELETE etc.) require admin access

    Main routes are specified in 'routes.php'

    Router logic supplied by bramus\router (https://github.com/bramus/router)
*/

$router->options('/(\S+)',function() {
    \Core\Headers::getHeaders();
}); // just return headers when OPTIONS call

/**************************************************************/
/* Before Router Middleware:                                  */
/* Auth Check: only logged-in users can access the api        */ 
/* https://github.com/bramus/router#before-router-middlewares */
/**************************************************************/
$router->before('GET|POST|PUT|DELETE|PATCH', '/.*', function() {
    
    $isAuthPath = \Core\Headers::path_is_auth();
    \Core\Headers::getHeaders($isAuthPath);

    // Don't do the logged-in check when it's an 'auth' path    
    if ( !$isAuthPath ) {
        $jwt = new \Models\JWTWrapper();

        if(!$jwt->loggedIn){      
            http_response_code(401);  
            echo json_encode(
                array("message" => "Not logged in.")
            );
            exit();
        }
    }
});

/**************************************************************/
/* Before Router Middleware:                                  */
/* Auth Check: only admin users can alter data                */ 
/* https://github.com/bramus/router#before-router-middlewares */
/**************************************************************/
$router->before('POST|PUT|DELETE|PATCH', '/.*', function() {

    $isAuthPath = \Core\Headers::path_is_auth();

    // Don't do the is-admin check when it's an 'auth' path    
    if ( !$isAuthPath ) {
        $jwt = new \Models\JWTWrapper();
        if (!$jwt->isAdmin){
            http_response_code(401);  
            echo json_encode(
                array("message" => "Must be an admin.")
            );
            exit(1);
        }
    }
});