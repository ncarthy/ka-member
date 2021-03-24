<?php

// Load the composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define core, database and headers helper class
include_once 'core/config.php';
include_once 'core/database.php';
include_once 'core/headers.php';

// Define models & controllers
include_once 'models/bank_account.php';
include_once 'models/country.php';
include_once 'models/jwt.php';
include_once 'models/member_filter.php';
include_once 'models/member_name.php';
include_once 'models/member.php';
include_once 'models/members.php';
include_once 'models/membership_status.php';
include_once 'models/payment_type.php';
include_once 'models/summary.php';
include_once 'models/transaction.php';
include_once 'models/user.php';
include_once 'models/usertoken.php';
include_once 'controllers/bank_account.controller.php';
include_once 'controllers/country.controller.php';
include_once 'controllers/member.controller.php';
include_once 'controllers/members.controller.php';
include_once 'controllers/name.controller.php';
include_once 'controllers/payment_type.controller.php';
include_once 'controllers/status.controller.php';
include_once 'controllers/summary.controller.php';
include_once 'controllers/transaction.controller.php';
include_once 'controllers/user.controller.php';

// Define routes
require 'pre_routes.php'; // Comment this io remove auth on API
require 'routes.php';

// Run it!
$router->run();