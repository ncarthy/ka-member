<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

//Check logged in
include_once '../objects/jwt.php';
$jwt = new JWTWrapper();
if(!$jwt->loggedIn){      
    http_response_code(401);  
    echo json_encode(
        array("message" => "Not logged in.")
    );
    exit(1);
}

// include database and object files
include_once '../config/database.php';
include_once '../objects/country.php';

// instantiate database and status object
$db = Database::getInstance()->conn;

// initialize object
$status = new Country($db);

// query
$stmt = $status->read();
$num = $stmt->rowCount();

// products array
$item_arr=array();

// check if more than 0 record found
if($num>0){
 
    $item_arr["count"]=$num;

    $item_arr["records"]=array(); // associative array keyed on 'id'
    //$item_arr["countries"]=array(); // un-keyed list

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
            $item=array(
                "id" => $id,
                "name" => $name
            );

            // create associative array keyed on id
            $item_arr["records"][$id] = $item;

            // create un-keyed list
            //array_push ($item_arr["countries"], $item);

        }

        echo json_encode($item_arr, JSON_NUMERIC_CHECK);
}
?>