<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
if($_SERVER['REQUEST_METHOD']=='OPTIONS') exit(0);

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

// include database and object files
include_once '../config/database.php';
include_once '../objects/member_name.php';

// instantiate database and user object
$db = Database::getInstance()->conn;

// initialize object
$items = new MemberName($db);

// set ID property of name to be edited
if (isset($_GET['idmember'])) {
    $items->idmember = $_GET['idmember'];
} else {
    http_response_code(422); 
    echo json_encode(
        array("message" => "Please supply the idmember as a parameter.")
    );
    exit(1);
}

// query database, return with dataset
$stmt = $items->readMemberNames();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $items_arr=array();
    $items_arr["count"]=$num;
    $items_arr["names"]=array();

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
                "honorific" => html_entity_decode($honorific),
                "firstname" => html_entity_decode($firstname),
                "surname" => html_entity_decode($surname),
                "idmember" => $idmember
            );

            array_push ($items_arr["names"], $item);
        }

        echo json_encode($items_arr, JSON_NUMERIC_CHECK);
}
?>