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

// include database and object files
include_once '../config/database.php';
include_once '../objects/members.php';

// instantiate database and member object
$database = new Database();
$db = $database->getConnection();

// initialize object
$member = new Members($db);

// set start and end of th eperiod to examine
// if no dates are provided then 
//  - the end date defaults to a string representing today e.g. '2021-02-09'
//  - the start date defaults to a string representing today minus 12 months 
//    plus one day e.g. '2020-02-10'
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start = isset($_GET['start']) ? $_GET['start'] : (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
$reportname = isset($_GET['report_name']) ? $_GET['report_name'] : '';

switch ($reportname) {
    case 'CEM':
        $stmt = $member->contributingExMembers($start, $end);
        break;
    case 'discounts':
        $stmt = $member->discountMembers($start, $end);
        break;
    case 'payingHonLife':
        $stmt = $member->payingHonLifeMembers($start, $end);
        break;
    case 'duplicate':
            $stmt = $member->membersPayingTwice($start, $end);
            break;
    default:
        http_response_code(422);  
        echo json_encode(
            array("message" => "Invalid or empty report name.")
        );
        exit(1);
}
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    $members_arr=array();
    $members_arr["count"] = $num; // add the count of rows
    $members_arr["records"]=array();

    $total_received = 0; // sum of member payments as we loop over rows
    $total_expected = 0; // sum of member fees as we loop over rows

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
    
        $members_item=array(
            "id" => $idmember,
            "type" => $membershiptype,
            "name" => $Name,
            "business" => $businessname,
            "expected_fee" => $membershipfee,
            "amount_received" => $amount,
            "date" => $date
        );

        $total_received+=$amount;
        $total_expected+=$membershipfee;

        // create un-keyed list
        array_push ($members_arr["records"], $members_item);
    }
    
    $members_arr["start"] = $start;
    $members_arr["end"] = $end;
    $members_arr["total"] = $total_received;

    // honorary and life members aren't expected to pay anything
    $members_arr["expected"] = $reportname=='payingHonLife'?0:$total_expected; 

    echo json_encode($members_arr, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
}
?>