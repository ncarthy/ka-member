<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Check logged in
include_once '../objects/jwt.php';
$jwt = new JWTWrapper();
if(!$jwt->loggedIn){      
    http_response_code(401);  
    echo json_encode(
        array("message" => "Not logged in.")
    );
    exit(1);
}
else if (!$jwt->isAdmin){
    http_response_code(401);  
    echo json_encode(
        array("message" => "Must be an admin.")
    );
    exit(1);
}

// include database and object files
include_once '../config/database.php';
include_once '../objects/member_filter.php';

// instantiate database and member object
$database = new Database();
$db = $database->getConnection();

// initialize object
$filter = new MemberFilter($db);
$filter->reset();

// get posted data
$data = json_decode(file_get_contents("php://input"));

// Go through each possible filter and apply each in turn
// This means all filters are 'AND' filters
if(isset($data->membertypeid)) {
    if (empty($data->membertypeid)) {
        // no filter applied
    } else {
        $filter->setMemberTypeID($data->membertypeid);
    }
}

// deletedate is a special case. When using a deletedate range we need to ignore the "removed" parameter
$deleteDateFilterIsSet = false;
if(isset($data->deletedatestart) || isset($data->deletedateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->deletedatestart) ? '' : $data->deletedatestart, 
                                !isset($data->deletedateend) ? '' : $data->deletedateend
                            );

    $filter->setDeleteRange($start, $end);
    $deleteDateFilterIsSet = true;
}

// UPDATE the row in the database
if($filter->anonymize($jwt->user)){
    echo '{';
        echo '"message": "Former members anonymized."';
    echo '}';
}

// if unable to create the new_item, tell the admin
else{
    http_response_code(422);
    echo '{';
        echo '"message": "Unable to anonymize former members.",';
        echo '"id":' . $item->id;
    echo '}';
}
?>