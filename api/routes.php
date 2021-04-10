<?php

/*
    Router logic supplied by bramus\router (https://github.com/bramus/router)

    Useing some example uses from https://github.com/wdekkers/raspberry-pi-app

    Regex cheat sheet: https://courses.cs.washington.edu/courses/cse154/15sp/cheat-sheets/php-regex-cheat-sheet.pdf

    I'm using three different ways of handinling routes:
        1. Pure funciton call. See _>before in 'pre_routes.php'
        2. Whole file like the post route for auth
        3. Method call like read_all for Bank Account

*/

// General config
$router->setNamespace('\Controllers'); // Allows us to omit '\Controllers' from method names

// Custom 404 Handler
$router->set404(function() {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  http_response_code(404);  
  echo json_encode(
      array("message" => "404, route not found!")
  );
});


/***************/
/* Auth Routes */
/***************/
/*$router->post('/auth', function () {
    require 'authenticate/auth.php'; 
} );*/
$router->mount('/auth', function() use ($router) {

    $router->post('/', function () {include 'authenticate/auth.php'; } );

    $router->get('/refresh', function () {include 'authenticate/refresh.php'; } );

    $router->delete('/revoke', function () {include 'authenticate/revoke.php'; } );
  
});
/***************/
/* Bank Routes */
/***************/
$router->mount('/bank_account', function () use ($router) {
    $router->get('/', 'BankAccountCtl@read_all');
    $router->get('/(\d+)', 'BankAccountCtl@read_one');
    $router->get('/(\D+)', 'BankAccountCtl@read_one_name');
});
/***************/
/* Country Routes */
/***************/
$router->mount('/country', function () use ($router) {

    // will result in '/memberstatus'
    $router->get('/', 'CountryCtl@read_all');

    // will result in '/memberstatus/id'
    $router->get('/(\d+)', 'CountryCtl@read_one');

    // will result in '/memberstatus/name'
    $router->get('/(\D+)', 'CountryCtl@read_one_name');

});
/*****************/
/* Member Routes */
/*****************/
$router->mount('/member', function () use ($router) {

    // will result in '/member'
    $router->get('/', 'MemberCtl@read_all');

    // will result in '/member/id'
    $router->get('/(\d+)', 'MemberCtl@read_one');

    // new member
    $router->post('/', 'MemberCtl@create');

    // delete member
    $router->delete('/(\d+)', 'MemberCtl@delete');

    // update member
    $router->put('/(\d+)', 'MemberCtl@update');

    // take action on member; One of 'settoformer', 'anonymize', 'setPrimaryGeometry' etc.
    $router->patch('/(\d+)', 'MemberCtl@patch');
    // PATCH vs PUT: https://stackoverflow.com/a/34400076/6941165
});
/*****************/
/* Members Routes */
/*****************/
$router->mount('/members', function () use ($router) {
    $router->get('/life_and_hon', 'MembersCtl@lifeAndHonorary');
    $router->get('/summary', 'MembersSummaryCtl@activeMembersByType');
    $router->get('/lapsed/(\d+)', 'MembersCtl@lapsed');
    $router->get('/cem', 'MembersCtl@cem');
    $router->get('/discount', 'MembersCtl@discount');
    $router->get('/payinghonlife', 'MembersCtl@payingHonLife');
    $router->get('/duplicatepayers', 'MembersCtl@duplicatepayers');
    $router->get('/mailinglist', 'MembersCtl@mailinglist');
    $router->get('/emaillist', 'MembersCtl@emaillist');
    $router->get('/maplist', 'MembersCtl@maplist');
    $router->get('/noukaddress', 'MembersCtl@noUKAddress');
    $router->get('/invalidemails', 'MembersCtl@invalidEmails');
    $router->get('/invalidpostcodes', 'MembersCtl@invalidPostcodes');
    $router->get('/deletedbutnotformer', 'MembersCtl@deletedButNotFormer');
    $router->get('/lapsedcem/(\d+)', 'MembersCtl@lapsedCEM');
    $router->get('/formermember/(\d+)', 'MembersCtl@formerMember');
    $router->get('/oldformermember/(\d+)', 'MembersCtl@oldFormerMember');
    $router->get('/filter', 'MembersCtl@filter');
    $router->patch('/lapsedcem/(\d+)', 'MembersCtl@patchLapsedCEM');
    $router->patch('/oldformermember/(\d+)', 'MembersCtl@patchOldFormerMember');
    //$router->patch('/', 'MembersCtl@patch');
});
/**********************/
/* Member Name Routes */
/**********************/
$router->mount('/name', function () use ($router) {

    // will result in '/name/id'
    $router->get('/(\d+)', 'MemberNameCtl@read_by_id');

    // will result in '/name/idmember/id'
    $router->get('/idmember/(\d+)', 'MemberNameCtl@read_by_idmember');

    // delete a single membername
    $router->delete('/(\d+)', 'MemberNameCtl@delete_by_id');

    // delete all names for a member
    $router->delete('/idmember/(\d+)', 'MemberNameCtl@delete_by_idmember');

    // update all names for a member
    $router->put('/idmember/(\d+)', 'MemberNameCtl@update_by_idmember');

    // new member name
    $router->post('/', 'MemberNameCtl@create');

    // update member name
    $router->put('/(\d+)', 'MemberNameCtl@update');
});
/***********************/
/* Payment Type Routes */
/***********************/
$router->mount('/payment_type', function () use ($router) {
    $router->get('/', 'PaymentTypeCtl@read_all');
    $router->get('/(\d+)', 'PaymentTypeCtl@read_one');
    $router->get('/(\D+)', 'PaymentTypeCtl@read_one_name');
});
/*****************/
/* Status Routes */
/*****************/
$router->mount('/status', function () use ($router) {

    // will result in '/memberstatus'
    $router->get('/', 'MembershipStatusCtl@read_all');

    // will result in '/memberstatus/id'
    $router->get('/(\d+)', 'MembershipStatusCtl@read_one');

    // will result in '/memberstatus/name'
    $router->get('/(\D+)', 'MembershipStatusCtl@read_one_name');

});
/**********************/
/* Transaction Routes */
/**********************/
$router->mount('/transaction', function () use ($router) {
    $router->get('/', 'TransactionCtl@read_all');
    $router->get('/(\d+)', 'TransactionCtl@read_one');
    $router->get('/idmember/(\d+)', 'TransactionCtl@read_by_idmember');
    $router->post('/', 'TransactionCtl@create');
    $router->put('/(\d+)', 'TransactionCtl@update');
    $router->delete('/(\d+)', 'TransactionCtl@delete_by_id');
    $router->delete('/idmember/(\d+)', 'TransactionCtl@delete_by_idmember');
});
/**********************/
/* Transactions Routes */
/**********************/
$router->mount('/transactions', function () use ($router) {
    $router->get('/summary', 'TransactionsCtl@summary_by_month');
    $router->get('/detail', 'TransactionsCtl@detail_by_month');
});
/***************/
/* User Routes */
/***************/
$router->mount('/user', function () use ($router) {

    // will result in '/user'
    $router->get('/', 'UserCtl@read_all');

    // will result in '/user/id'
    $router->get('/(\d+)', 'UserCtl@read_one');

    // new user
    $router->post('/', 'UserCtl@create');

    // delete user
    $router->delete('/(\d+)', 'UserCtl@delete');

    // update user
    $router->put('/(\d+)', 'UserCtl@update');
});

