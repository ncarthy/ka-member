<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

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

// get database connection
$db = Database::getInstance()->conn;

// prepare takings object
$item = new MemberName($db);

// set ID property of name to be edited
if (isset($_GET['idmember'])) {
    $item->idmember = $_GET['idmember'];
}
else 
{
    http_response_code(422); 
    echo json_encode(
        array("message" => "Please supply the idmember as a parameter.")
    );
    exit(1);
}

// query
$stmt = $item->readMemberNames();
$num = $stmt->rowCount();

// array
$items_arr=array();

// check if more than 0 record found
if($num>0){
 
    $items_arr["count"] = $num;
    $items_arr["records"]=array();

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
                "honorific" => $honorific,
                "firstname" => $firstname,
                "surname" => $surname,
                "idmember" => $idmember
            );

            // create un-keyed list
            array_push ($items_arr["records"], $item);
        }
} else {
    http_response_code(422); 
    echo json_encode(
        array("message" => "No names found with that idmember.",
                "idmember" => $item->idmember), JSON_NUMERIC_CHECK
    );
    exit(1);    
}



// make it json format
echo json_encode($items_arr, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
?>