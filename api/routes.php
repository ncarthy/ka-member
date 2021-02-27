<?php

$router->setNamespace('\Controllers');

/**************/
/* Pre-Router */
/* Auth Check */ 
/**************/
$router->before('GET|POST|PUT|DELETE|PATCH', '/.*', function() {
    
    $path_only = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $first_part_of_path = substr( $path_only, 1, 4 );

    // Only check if logged in if non-'auth' path
    if ($first_part_of_path != "auth") {
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
/***************/
/* Auth Routes */
/***************/
$router->post('/auth', function () {
    require 'authenticate/auth.php'; 
} );
$router->mount('/authenticate', function() use ($router) {

    $router->post('/auth', function () {include 'authenticate/auth.php'; } );
  
  });
/***************/
/* Status Routes */
/***************/
$router->mount('/status', function () use ($router) {

    // will result in '/memberstatus'
    $router->get('/', 'MembershipStatusCtl@read_all');

    // will result in '/memberstatus/id'
    $router->get('/(\d+)', 'MembershipStatusCtl@read_one');

    // will result in '/memberstatus/name'
    $router->get('/(\D+)', 'MembershipStatusCtl@read_one_name');

});

/***************/
/* User Routes */
/***************/
$router->mount('/user', function () use ($router) {

    // will result in '/user'
    $router->get('/', 'UserCtl@read_all');

    // will result in '/user/id'
    $router->get('/(\d+)', 'UserCtl@read_one');

});

$router->options('/(\S+)','');

// Custom 404 Handler
$router->set404(function() {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  echo '404, route not found!';
});