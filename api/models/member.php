<?php
namespace Models;
use \PDO;
class Member{
    // database conn 
    private $conn;
    // table name
    private $table_name = "member";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $id;
    public $title;
    public $businessname;
    public $bankpayerref;
    public $note;
    public $addressfirstline;
    public $addresssecondline;
    public $city;
    public $county;
    public $postcode;
    public $countryID;
    public $area;
    public $email1;
    public $phone1;
    public $addressfirstline2;
    public $addresssecondline2;
    public $city2;
    public $county2;
    public $postcode2;
    public $country2ID;
    public $email2;
    public $phone2;
    public $statusID;
    public $expirydate;
    public $joindate;
    public $reminderdate;
    public $updatedate;
    public $deletedate;
    public $repeatpayment;
    public $recurringpayment;
    public $username;
    public $gdpr_email;
    public $gdpr_tel;
    public $gdpr_address;
    public $gdpr_sm;
    public $postonhold;

    public function read(){
        
        $member_arr=array();

        //select all data
        $query = "SELECT
                    idmember as `id`, title, businessname, bankpayerref, note, addressfirstline,
                    addresssecondline, city, county, postcode, countryID, area, addressfirstline2,
                    addresssecondline2, city2, county2, postcode2, country2ID, email1, email2,
                    phone1, phone2, membership_idmembership as `statusID`, expirydate, joindate, 
                    updatedate, deletedate, repeatpayment, recurringpayment, username, gdpr_email, 
                    gdpr_tel, gdpr_address, gdpr_sm, reminderdate, postonhold
                    FROM
                    " . $this->table_name;                    

        $stmt = $this->conn->prepare( $query );
        try{
            // execute query
            $stmt->execute();
            $num = $stmt->rowCount();
            
            // check if more than 0 record found
            if($num>0){        
                // retrieve our table contents
                // fetch() is faster than fetchAll()
                // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    // extract row
                    // this will make $row['name'] to
                    // just $name only
                    extract($row);
                
                    $member_item=array(
                        "id" => $id,
                        "title" => $title,
                        "businessname" => html_entity_decode($businessname),
                        "bankpayerref" => html_entity_decode($bankpayerref),
                        "note" => html_entity_decode($note),
                        "addressfirstline" => html_entity_decode($addressfirstline),
                        "addresssecondline" => html_entity_decode($addresssecondline),
                        "city" => html_entity_decode($city),
                        "county" => html_entity_decode($county),
                        "postcode" => html_entity_decode($postcode),
                        "countryID" => $countryID,
                        "area" => html_entity_decode($area),
                        "email1" => html_entity_decode($email1),
                        "phone1" => html_entity_decode('xn#'.$phone1),// Substitution trick to preserve phone numbers as strings
                        "addressfirstline2" => html_entity_decode($addressfirstline2),
                        "addresssecondline2" => html_entity_decode($addresssecondline2),
                        "city2" => html_entity_decode($city2),
                        "county2" => html_entity_decode($county2),
                        "postcode2" => html_entity_decode($postcode2),
                        "country2ID" => $country2ID,
                        "email2" => html_entity_decode($email2),
                        "phone2" => html_entity_decode('xn#'.$phone2),// Substitution trick to preserve phone numbers as strings
                        "statusID" => $statusID,
                        "expirydate" => $expirydate,
                        "joindate" => $joindate,
                        "reminderdate" => $reminderdate,
                        "updatedate" => $updatedate,
                        "deletedate" => $deletedate,
                        "repeatpayment" => $repeatpayment,
                        "recurringpayment" => $recurringpayment,
                        "username" => $username,
                        "gdpr_email" => $gdpr_email?true:false,
                        "gdpr_tel" => $gdpr_tel?true:false,
                        "gdpr_address" => $gdpr_address?true:false,
                        "gdpr_sm" => $gdpr_sm?true:false,
                        "postonhold" => $postonhold?true:false
                    );

                    array_push($member_arr, $member_item);

                }    
            }
        }
        catch(PDOException $exception){
            echo "Error retrieving members: " . $exception->getMessage();
        }
        
        return $member_arr;
    }

        // find the details of one user using $id
        public function readOne(){

            //select all data
            $query = "SELECT
                        idmember as `id`, title, businessname, bankpayerref, note, addressfirstline,
                        addresssecondline, city, county, postcode, countryID, area, addressfirstline2,
                        addresssecondline2, city2, county2, postcode2, country2ID, email1, email2,
                        phone1, phone2, membership_idmembership as `statusID`, expirydate, joindate, 
                        updatedate, deletedate, repeatpayment, recurringpayment, username, gdpr_email, 
                        gdpr_tel, gdpr_address, gdpr_sm, reminderdate, postonhold
                        FROM
                        " . $this->table_name . " 
                        WHERE idmember = ?
                        LIMIT 0,1";
                    
            // prepare query statement
            $stmt = $this->conn->prepare( $query );
    
            // bind id of product to be updated
            $stmt->bindParam(1, $this->id);
    
            // execute query
            $stmt->execute();
    
            // get retrieved row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // set values to object properties
            if ( !empty($row) ) {
                $this->id = $row['id'];
                $this->title = $row['title'];
                $this->businessname = $row['businessname'];
                $this->bankpayerref = $row['bankpayerref'];
                $this->note = $row['note'];
                $this->addressfirstline = $row['addressfirstline'];
                $this->addresssecondline = $row['addresssecondline'];
                $this->city = $row['city'];
                $this->county = $row['county'];
                $this->postcode = $row['postcode'];
                $this->countryID = $row['countryID'];
                $this->area = $row['area'];
                $this->email1 = $row['email1'];
                $this->phone1 = $row['phone1'];
                $this->addressfirstline2 = $row['addressfirstline2'];
                $this->addresssecondline2 = $row['addresssecondline2'];
                $this->city2 = $row['city2'];
                $this->county2 = $row['county2'];
                $this->postcode2 = $row['postcode2'];
                $this->country2ID = $row['country2ID'];
                $this->email2 = $row['email2'];
                $this->phone2 = $row['phone2'];
                $this->statusID = $row['statusID'];
                $this->expirydate = $row['expirydate'];
                $this->joindate = $row['joindate'];
                $this->reminderdate = $row['reminderdate'];
                $this->updatedate = $row['updatedate'];
                $this->deletedate = $row['deletedate'];
                $this->repeatpayment = $row['repeatpayment'];
                $this->recurringpayment = $row['recurringpayment'];
                $this->username = $row['username'];
                $this->gdpr_email = $row['gdpr_email']?true:false;
                $this->gdpr_tel = $row['gdpr_tel']?true:false;
                $this->gdpr_address = $row['gdpr_address']?true:false;
                $this->gdpr_sm = $row['gdpr_sm']?true:false;            
                $this->postonhold = $row['postonhold']?true:false;       
            }
        }

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    title=:title, 
                    businessname=:businessname, 
                    bankpayerref=:bankpayerref, 
                    note=:note, 
                    addressfirstline=:addressfirstline, 
                    addresssecondline=:addresssecondline, 
                    city=:city, 
                    county=:county, 
                    postcode=:postcode,
                    countryID=:countryID, 
                    area=:area,
                    email1=:email1, 
                    phone1=:phone1, 
                    addressfirstline2=:addressfirstline2, 
                    addresssecondline2=:addresssecondline2, 
                    city2=:city2, 
                    county2=:county2, 
                    postcode2=:postcode2,
                    country2ID=:country2ID, 
                    email2=:email2, 
                    phone2=:phone2, 
                    membership_idmembership=:statusID, 
                    expirydate=:expirydate, 
                    joindate=:joindate, 
                    reminderdate=:reminderdate,
                    updatedate=:updatedate, 
                    deletedate=:deletedate, 
                    repeatpayment=:repeatpayment, 
                    recurringpayment=:recurringpayment, 
                    username=:username, 
                    gdpr_email=:gdpr_email, 
                    gdpr_tel=:gdpr_tel, 
                    gdpr_address=:gdpr_address, 
                    gdpr_sm=:gdpr_sm,
                    postonhold=:postonhold
                    ;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->sanitize_inputs();
        $gdpr_email = $this->gdpr_email?1:0;
        $gdpr_tel = $this->gdpr_tel?1:0;
        $gdpr_address = $this->gdpr_address?1:0;
        $gdpr_sm = $this->gdpr_sm?1:0;
        $postonhold = $this->postonhold?1:0;

        // bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":businessname", $this->businessname);
        $stmt->bindParam(":bankpayerref", $this->bankpayerref);
        $stmt->bindParam(":note", $this->note);
        $stmt->bindParam(":addressfirstline", $this->addressfirstline);
        $stmt->bindParam(":addresssecondline", $this->addresssecondline);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":county", $this->county);
        $stmt->bindParam(":postcode", $this->postcode);
        $stmt->bindParam(":countryID", $this->countryID);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":email1", $this->email1);
        $stmt->bindParam(":phone1", $this->phone1);
        $stmt->bindParam(":addressfirstline2", $this->addressfirstline2);
        $stmt->bindParam(":addresssecondline2", $this->addresssecondline2);
        $stmt->bindParam(":city2", $this->city2);
        $stmt->bindParam(":county2", $this->county2);
        $stmt->bindParam(":postcode2", $this->postcode2);
        $stmt->bindParam(":country2ID", $this->country2ID);
        $stmt->bindParam(":email2", $this->email2);
        $stmt->bindParam(":phone2", $this->phone2);
        $stmt->bindParam(":statusID", $this->statusID);
        $stmt->bindParam(":expirydate", $this->expirydate);
        $stmt->bindParam(":joindate", $this->joindate);
        $stmt->bindParam(":reminderdate", $this->reminderdate);
        $stmt->bindParam(":updatedate", $this->updatedate);
        $stmt->bindParam(":deletedate", $this->deletedate);
        $stmt->bindParam(":repeatpayment", $this->repeatpayment);
        $stmt->bindParam(":recurringpayment", $this->recurringpayment);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":gdpr_email", $gdpr_email);
        $stmt->bindParam(":gdpr_tel", $gdpr_tel);
        $stmt->bindParam(":gdpr_address", $gdpr_address);
        $stmt->bindParam(":gdpr_sm", $gdpr_sm);
        $stmt->bindParam(":postonhold", $postonhold);
        
        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            if($this->id) {
                return true;
            } else {
                return false;
            }
        }
        
        return false;
    }

    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    title=:title, 
                    businessname=:businessname, 
                    bankpayerref=:bankpayerref, 
                    note=:note, 
                    addressfirstline=:addressfirstline, 
                    addresssecondline=:addresssecondline, 
                    city=:city, 
                    county=:county, 
                    postcode=:postcode, 
                    countryID=:countryID, 
                    area=:area, 
                    email1=:email1, 
                    phone1=:phone1, 
                    addressfirstline2=:addressfirstline2, 
                    addresssecondline2=:addresssecondline2, 
                    city2=:city2, 
                    county2=:county2, 
                    postcode2=:postcode2, 
                    country2ID=:country2ID, 
                    email2=:email2, 
                    phone2=:phone2, 
                    membership_idmembership=:statusID, 
                    expirydate=:expirydate, 
                    joindate=:joindate, 
                    reminderdate=:reminderdate,
                    updatedate=:updatedate, 
                    deletedate=:deletedate, 
                    repeatpayment=:repeatpayment, 
                    recurringpayment=:recurringpayment, 
                    username=:username, 
                    gdpr_email=:gdpr_email, 
                    gdpr_tel=:gdpr_tel, 
                    gdpr_address=:gdpr_address, 
                    gdpr_sm=:gdpr_sm,
                    postonhold=:postonhold                    
                 WHERE
                    idmember=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        $this->sanitize_inputs();
        $gdpr_email = $this->gdpr_email?1:0;
        $gdpr_tel = $this->gdpr_tel?1:0;
        $gdpr_address = $this->gdpr_address?1:0;
        $gdpr_sm = $this->gdpr_sm?1:0;
        $postonhold = $this->postonhold?1:0;

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":businessname", $this->businessname);
        $stmt->bindParam(":bankpayerref", $this->bankpayerref);
        $stmt->bindParam(":note", $this->note);
        $stmt->bindParam(":addressfirstline", $this->addressfirstline);
        $stmt->bindParam(":addresssecondline", $this->addresssecondline);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":county", $this->county);
        $stmt->bindParam(":postcode", $this->postcode);
        $stmt->bindParam(":countryID", $this->countryID);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":email1", $this->email1);
        $stmt->bindParam(":phone1", $this->phone1);
        $stmt->bindParam(":addressfirstline2", $this->addressfirstline2);
        $stmt->bindParam(":addresssecondline2", $this->addresssecondline2);
        $stmt->bindParam(":city2", $this->city2);
        $stmt->bindParam(":county2", $this->county2);
        $stmt->bindParam(":postcode2", $this->postcode2);
        $stmt->bindParam(":country2ID", $this->country2ID);
        $stmt->bindParam(":email2", $this->email2);
        $stmt->bindParam(":phone2", $this->phone2);
        $stmt->bindParam(":statusID", $this->statusID);
        $stmt->bindParam(":expirydate", $this->expirydate);
        $stmt->bindParam(":joindate", $this->joindate);
        $stmt->bindParam(":reminderdate", $this->reminderdate);
        $stmt->bindParam(":updatedate", $this->updatedate);
        $stmt->bindParam(":deletedate", $this->deletedate);
        $stmt->bindParam(":repeatpayment", $this->repeatpayment);
        $stmt->bindParam(":recurringpayment", $this->recurringpayment);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":gdpr_email", $gdpr_email);
        $stmt->bindParam(":gdpr_tel", $gdpr_tel);
        $stmt->bindParam(":gdpr_address", $gdpr_address);
        $stmt->bindParam(":gdpr_sm", $gdpr_sm);
        $stmt->bindParam(":postonhold", $postonhold);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    function anonymize(){
        /* sanitize */
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);

        /* Remove name */
        $query = "DELETE FROM `membername` WHERE member_idmember = " . $this->id . " ;";
        $this->conn->query($query);
        /* Insert dummy name */
        $query = "INSERT INTO `membername` 
            ( `honorific`, `firstname`, `surname`, `member_idmember`) 
            VALUES ('', '', 'Anonymized', " . $this->id . ");";
        $this->conn->query($query);


        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    note='',
                    addressfirstline='', 
                    addresssecondline='', 
                    city='', 
                    county='', 
                    postcode='', 
                    countryID=NULL, 
                    area='', 
                    email1='', 
                    phone1='', 
                    addressfirstline2='', 
                    addresssecondline2='', 
                    city2='', 
                    county2='', 
                    postcode2='', 
                    country2ID=NULL, 
                    email2='', 
                    phone2='', 
                    updatedate= NULL, 
                    username=:username                  
                 WHERE
                    idmember=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $this->username);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    /* update membership status from  e.g. Individual to 'Former Member' */
    function setToFormerMember(){
        /* sanitize */
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);

        $query = "UPDATE
                    " . $this->table_name . "
                    SET `membership_idmembership` = 
                        (SELECT idmembership FROM `membershipstatus` WHERE name LIKE 'former%'),
                        updatedate= NULL, 
                        deletedate=CURDATE(),
                        username=:username  
                    WHERE idmember=:id; ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $this->username);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }


    // Delete one member from the database
    function delete(){
        /* sanitize */
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);

        /* delete from FK-lined tables first */
        $query = "DELETE FROM `transaction` WHERE member_idmember = " . $this->id . " ;";
        $this->conn->query($query);
        $query = "DELETE FROM `membername` WHERE member_idmember = " . $this->id . " ;";
        $this->conn->query($query);

        /* Now delete from member table */
        $query = "DELETE FROM " . $this->table_name . " WHERE idmember = ?";
        $stmt = $this->conn->prepare($query);        
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // used for paging products
    public function count(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
    
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_rows'];
    }

    private function sanitize_inputs(){
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->businessname=htmlspecialchars(strip_tags($this->businessname));
        $this->bankpayerref=htmlspecialchars(strip_tags($this->bankpayerref));
        $this->note=htmlspecialchars(strip_tags($this->note));
        $this->addressfirstline=htmlspecialchars(strip_tags($this->addressfirstline));
        $this->addresssecondline=htmlspecialchars(strip_tags($this->addresssecondline));
        $this->city=htmlspecialchars(strip_tags($this->city));
        $this->county=htmlspecialchars(strip_tags($this->county));
        $this->postcode=htmlspecialchars(strip_tags($this->postcode));
        $this->countryID=filter_var($this->countryID, FILTER_SANITIZE_NUMBER_INT);
        $this->area=htmlspecialchars(strip_tags($this->area));
        $this->email1=htmlspecialchars(strip_tags($this->email1));
        $this->phone1=htmlspecialchars(strip_tags($this->phone1));
        $this->addressfirstline2=htmlspecialchars(strip_tags($this->addressfirstline2));
        $this->addresssecondline2=htmlspecialchars(strip_tags($this->addresssecondline2));
        $this->city2=htmlspecialchars(strip_tags($this->city2));
        $this->county2=htmlspecialchars(strip_tags($this->county2));
        $this->postcode2=htmlspecialchars(strip_tags($this->postcode2));
        $this->country2ID=filter_var($this->country2ID, FILTER_SANITIZE_NUMBER_INT);
        $this->email2=htmlspecialchars(strip_tags($this->email2));
        $this->phone2=htmlspecialchars(strip_tags($this->phone2));
        $this->statusID=htmlspecialchars(strip_tags($this->statusID));
        $this->expirydate=htmlspecialchars(strip_tags($this->expirydate));
        $this->joindate=htmlspecialchars(strip_tags($this->joindate));
        $this->reminderdate=htmlspecialchars(strip_tags($this->reminderdate));
        $this->updatedate=htmlspecialchars(strip_tags($this->updatedate));
        $this->deletedate=htmlspecialchars(strip_tags($this->deletedate));
        $this->repeatpayment=htmlspecialchars(strip_tags($this->repeatpayment));
        $this->recurringpayment=htmlspecialchars(strip_tags($this->recurringpayment));
        $this->username=htmlspecialchars(strip_tags($this->username));
        
        $this->expirydate = !empty($this->expirydate) ? $this->expirydate : NULL;
        $this->joindate = !empty($this->joindate) ? $this->joindate : NULL;
        $this->reminderdate = !empty($this->reminderdate) ? $this->reminderdate : NULL;
        $this->updatedate = !empty($this->updatedate) ? $this->updatedate : NULL;
        $this->deletedate = !empty($this->deletedate) ? $this->deletedate : NULL;        
        $this->countryID = !empty($this->countryID) ? $this->countryID : NULL;
        $this->country2ID = !empty($this->country2ID) ? $this->country2ID : NULL;
    }
}
?>