<?php
// CORS when your origin is not localhost you are going to need this or else it will not work.
// if you are using it outside of your home network please reconsider the * of allow origin
include_once 'config/core.php';
header("Access-Control-Allow-Origin: ". \Core\Config::read('server'));
header("Access-Control-Allow-Methods: POST, PUT, DELETE, GET, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, auth, user");
header("Access-Control-Max-Age: 1728000");
header("Content-Type: application/json; charset=UTF-8");

// Load the composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// include database and object files

// Define models
include_once 'config/database.php';
include_once 'models/bank_account.php';
include_once 'models/country.php';
include_once 'models/jwt.php';
include_once 'models/member_filter.php';
include_once 'models/member_name.php';
include_once 'models/member.php';
include_once 'models/members.php';
include_once 'models/membership_status.php';
include_once 'models/transaction.php';
include_once 'models/user.php';
include_once 'models/usertoken.php';
include_once 'controllers/bank_account.controller.php';
include_once 'controllers/country.controller.php';
include_once 'controllers/member.controller.php';
include_once 'controllers/status.controller.php';
include_once 'controllers/user.controller.php';

// Define routes
require 'pre_routes.php'; // Comment this io remove auth on API
require 'routes.php';

// Run it!
$router->run();