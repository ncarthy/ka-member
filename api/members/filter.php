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
if(isset($data->surname)) {
    if (empty($data->surname)) {
        // no filter applied
    } else {
        $filter->setSurname($data->surname);
    }
}
if(isset($data->businessname)) {
    if (empty($data->businessname)) {
        // no filter applied
    } else {
        $filter->setBusinessname($data->businessname);
    }
}
if(isset($data->membertypeid)) {
    if (empty($data->membertypeid)) {
        // no filter applied
    } else {
        $filter->setMemberTypeID($data->membertypeid);
    }
}
if(isset($data->email1)) {
    if (empty($data->email1) || $data->email1 =='any') {    
        // no filter applied
    } else {
        $filter->setEmail1($data->email1);
    }
}
if(isset($data->addressfirstline)) {
    if (empty($data->addressfirstline) || $data->addressfirstline =='any') {    
        // no filter applied
    } else {
        $filter->setAddress($data->addressfirstline);
    }
}
if(isset($data->paymentmethod)) {
    if (empty($data->paymentmethod) || $data->paymentmethod =='any') {    
        // no filter applied
    } else {
        $filter->setPaymentMethod($data->paymentmethod);
    }
}

if(isset($data->expirydatestart) || isset($data->expirydateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->expirydatestart) ? '' : $data->expirydatestart, 
                                !isset($data->expirydateend) ? '' : $data->expirydateend
                            );

    $filter->setExpiryRange($start, $end);
}
if(isset($data->joindatestart) || isset($data->joindateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->joindatestart) ? '' : $data->joindatestart, 
                                !isset($data->joindateend) ? '' : $data->joindateend
                            );

    $filter->setJoinRange($start, $end);
}
if(isset($data->reminderdatestart) || isset($data->reminderdateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->reminderdatestart) ? '' : $data->reminderdatestart, 
                                !isset($data->reminderdateend) ? '' : $data->reminderdateend
                            );

    $filter->setReminderRange($start, $end);
}
if(isset($data->updatedatestart) || isset($data->updatedateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->updatedatestart) ? '' : $data->updatedatestart, 
                                !isset($data->updatedateend) ? '' : $data->updatedateend
                            );

    $filter->setUpdateRange($start, $end);
}
if(isset($data->lasttransactiondatestart) || isset($data->lasttransactiondateend)) {
    $start='';
    $end='';
    list($start, $end) = $filter->sanitizeDateValues(
                                !isset($data->lasttransactiondatestart) ? '' : $data->lasttransactiondatestart, 
                                !isset($data->lasttransactiondateend) ? '' : $data->lasttransactiondateend
                            );

    $filter->setLastTransactionRange($start, $end);    
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

// Normally you only want to view the un-deleted members
// and that is the defaul setting.
// So if "removed" is set to 0 or is missing then only
// non-deleted members will appear in the list
// if removed is set to 'any' then no filter applied
if (isset($data->removed)) {
    if ($data->removed =='any') {    
        // no filter applied
    } else {
        if ($data->removed || $data->removed == 'y') {
            $filter->setDeleted();
        } else if ($deleteDateFilterIsSet) {
            // filter already applied
        } else {
            $filter->setNotDeleted();
            
        }
    }
} else if ($deleteDateFilterIsSet) {
    // filter already applied
} else {
    $filter->setNotDeleted();
    
}

$stmt=$filter->execute();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
 
    $members_arr=array();
    $members_arr["count"] = $num; // add the count of rows
    $members_arr["records"]=array();

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
            "idmembership" => $idmembership,
            "type" => $membershiptype,
            "name" => $name,
            "business" => $businessname,
            "note" => $note,
            "addressfirstline" => $addressfirstline,
            "addresssecondline" => $addresssecondline,
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country,
            "gdpr_email" => $gdpr_email,
            "gdpr_tel" => $gdpr_tel,
            "gdpr_address" => $gdpr_address,
            "gdpr_sm" => $gdpr_sm,
            "expirydate" => $expirydate,
            "joindate" => $joindate,
            "reminderdate" => $reminderdate,
            "updatedate" => $updatedate,
            "deletedate" => $deletedate,
            "paymentmethod" => $paymentmethod,
            "lasttransactiondate" => $lasttransactiondate,
            "email1" => $email1
        );

        // create un-keyed list
        array_push ($members_arr["records"], $members_item);

        if (isset($start)){
            $members_arr["startdate"] = $start; // the starting date of a date range
        }
        if (isset($end)){
            $members_arr["enddate"] = $end; // the starting date of a date range
        }
    }    

    echo json_encode($members_arr, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
}
?>