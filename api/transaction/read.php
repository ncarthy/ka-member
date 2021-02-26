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
include_once '../objects/transaction.php';

// instantiate database and transaction object
$db = Database::getInstance()->conn;

// initialize object
$item = new Transaction($db);

// set idmember property of transaction, if it exists.
// Then query database, return with dataset
if( isset($_GET['idmember']) ) {
    $item->idmember = $_GET['idmember'];
    $stmt = $item->readMember($item->idmember);
} else {
    $stmt = $item->readAll();
}

$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    // products array
    $items_arr=array();
    $items_arr["count"]=$num;
    $items_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
            $item_item=array(
                "idtransaction" => $id,
                "date" => $date,
                "amount" => $amount,
                "paymentmethod" => html_entity_decode($paymentmethod),
                "idmember" => $idmember,
                "bankID" => $bankID
            );

            // create associative array keyed on username
            $items_arr["records"][$id] = $item_item;
        }

        echo json_encode($items_arr, JSON_NUMERIC_CHECK);
}
?>