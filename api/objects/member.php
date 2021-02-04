<?php
class Member{
    // database conn 
    private $conn;
    // table name
    private $table_name = "member";

    public function __construct($db){
        $this->conn = $db;
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
    public $country;
    public $area;
    public $email1;
    public $phone1;
    public $addressfirstline2;
    public $addresssecondline2;
    public $city2;
    public $county2;
    public $postcode2;
    public $country2;
    public $email2;
    public $phone2;
    public $statusID;
    public $expirydate;
    public $joindate;
    public $updatedate;
    public $deletedate;
    public $repeatpayment;
    public $recurringpayment;
    public $username;
    public $gdpr_email;
    public $gdpr_tel;
    public $gdpr_address;
    public $gdpr_sm;

    // used by select drop-down list
    public function readAll(){

        //select all data
        $query = "SELECT
                    idmember as `id`, title, businessname, bankpayerref, note, addressfirstline,
                    addresssecondline, city, county, postcode, country, area, addressfirstline2,
                    addresssecondline2, city2, county2, postcode2, country2, email1, email2,
                    phone1, phone2, membership_idmembership as `statusID`, expirydate, joindate, 
                    updatedate, deletedate, repeatpayment, recurringpayment, username, gdpr_email, 
                    gdpr_tel, gdpr_address, gdpr_sm
                    FROM
                    " . $this->table_name;                    

        $stmt = $this->conn->prepare( $query );
        try{
            // execute query
            $stmt->execute();
        }
        catch(PDOException $exception){
            echo "Error retrieving users: " . $exception->getMessage();
        }
        
        return $stmt;
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
                    country=:country, 
                    area=:area,
                    email1=:email1, 
                    phone1=:phone1, 
                    addressfirstline2=:addressfirstline2, 
                    addresssecondline2=:addresssecondline2, 
                    city2=:city2, 
                    county2=:county2, 
                    postcode2=:postcode2,
                    country2=:country2, 
                    email2=:email2, 
                    phone2=:phone2, 
                    membership_idmembership=:statusID, 
                    expirydate=:expirydate, 
                    joindate=:joindate, 
                    updatedate=:updatedate, 
                    deletedate=:deletedate, 
                    repeatpayment=:repeatpayment, 
                    recurringpayment=:recurringpayment, 
                    username=:username, 
                    gdpr_email=:gdpr_email, 
                    gdpr_tel=:gdpr_tel, 
                    gdpr_address=:gdpr_address, 
                    gdpr_sm=:gdpr_sm
                    ;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->businessname=htmlspecialchars(strip_tags($this->businessname));
        $this->bankpayerref=htmlspecialchars(strip_tags($this->bankpayerref));
        $this->note=htmlspecialchars(strip_tags($this->note));
        $this->addressfirstline=htmlspecialchars(strip_tags($this->addressfirstline));
        $this->addresssecondline=htmlspecialchars(strip_tags($this->addresssecondline));
        $this->city=htmlspecialchars(strip_tags($this->city));
        $this->county=htmlspecialchars(strip_tags($this->county));
        $this->postcode=htmlspecialchars(strip_tags($this->postcode));
        $this->country=htmlspecialchars(strip_tags($this->country));
        $this->area=htmlspecialchars(strip_tags($this->area));
        $this->email1=htmlspecialchars(strip_tags($this->email1));
        $this->phone1=htmlspecialchars(strip_tags($this->phone1));
        $this->addressfirstline2=htmlspecialchars(strip_tags($this->addressfirstline2));
        $this->addresssecondline2=htmlspecialchars(strip_tags($this->addresssecondline2));
        $this->city2=htmlspecialchars(strip_tags($this->city2));
        $this->county2=htmlspecialchars(strip_tags($this->county2));
        $this->postcode2=htmlspecialchars(strip_tags($this->postcode2));
        $this->country2=htmlspecialchars(strip_tags($this->country2));
        $this->email2=htmlspecialchars(strip_tags($this->email2));
        $this->phone2=htmlspecialchars(strip_tags($this->phone2));
        $this->statusID=htmlspecialchars(strip_tags($this->statusID));
        $this->expirydate=htmlspecialchars(strip_tags($this->expirydate));
        $this->joindate=htmlspecialchars(strip_tags($this->joindate));
        $this->updatedate=htmlspecialchars(strip_tags($this->updatedate));
        $this->deletedate=htmlspecialchars(strip_tags($this->deletedate));
        $this->repeatpayment=htmlspecialchars(strip_tags($this->repeatpayment));
        $this->recurringpayment=htmlspecialchars(strip_tags($this->recurringpayment));
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->gdpr_email=htmlspecialchars(strip_tags($this->gdpr_email));
        $this->gdpr_tel=htmlspecialchars(strip_tags($this->gdpr_tel));
        $this->gdpr_address=htmlspecialchars(strip_tags($this->gdpr_address));
        $this->gdpr_sm=htmlspecialchars(strip_tags($this->gdpr_sm));
        
        $this->expirydate = !empty($this->expirydate) ? $this->expirydate : NULL;
        $this->joindate = !empty($this->joindate) ? $this->joindate : NULL;
        $this->updatedate = !empty($this->updatedate) ? $this->updatedate : NULL;
        $this->deletedate = !empty($this->deletedate) ? $this->deletedate : NULL;

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
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":email1", $this->email1);
        $stmt->bindParam(":phone1", $this->phone1);
        $stmt->bindParam(":addressfirstline2", $this->addressfirstline2);
        $stmt->bindParam(":addresssecondline2", $this->addresssecondline2);
        $stmt->bindParam(":city2", $this->city2);
        $stmt->bindParam(":county2", $this->county2);
        $stmt->bindParam(":postcode2", $this->postcode2);
        $stmt->bindParam(":country2", $this->country2);
        $stmt->bindParam(":email2", $this->email2);
        $stmt->bindParam(":phone2", $this->phone2);
        $stmt->bindParam(":statusID", $this->statusID);
        $stmt->bindParam(":expirydate", $this->expirydate);
        $stmt->bindParam(":joindate", $this->joindate);
        $stmt->bindParam(":updatedate", $this->updatedate);
        $stmt->bindParam(":deletedate", $this->deletedate);
        $stmt->bindParam(":repeatpayment", $this->repeatpayment);
        $stmt->bindParam(":recurringpayment", $this->recurringpayment);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":gdpr_email", $this->gdpr_email);
        $stmt->bindParam(":gdpr_tel", $this->gdpr_tel);
        $stmt->bindParam(":gdpr_address", $this->gdpr_address);
        $stmt->bindParam(":gdpr_sm", $this->gdpr_sm);
        
        
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
/*
    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    suspended=:suspended,
                    name=:fullname
                    " . (isset($this->password)?',new_pass=:password ':'') ."
                 WHERE
                    iduser=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->isadmin=htmlspecialchars(strip_tags($this->isadmin));
        $this->suspended=htmlspecialchars(strip_tags($this->suspended));
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $this->isadmin);
        $stmt->bindParam(":suspended", $this->suspended);
        $stmt->bindParam(":fullname", $this->fullname);        

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }*/

    // find the details of one user using $id
    public function readOne(){

        //select all data
        $query = "SELECT
                    idmember as `id`, title, businessname, bankpayerref, note, addressfirstline,
                    addresssecondline, city, county, postcode, country, area, addressfirstline2,
                    addresssecondline2, city2, county2, postcode2, country2, email1, email2,
                    phone1, phone2, membership_idmembership as `statusID`, expirydate, joindate, 
                    updatedate, deletedate, repeatpayment, recurringpayment, username, gdpr_email, 
                    gdpr_tel, gdpr_address, gdpr_sm
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
            $this->country = $row['country'];
            $this->area = $row['area'];
            $this->email1 = $row['email1'];
            $this->phone1 = $row['phone1'];
            $this->addressfirstline2 = $row['addressfirstline2'];
            $this->addresssecondline2 = $row['addresssecondline2'];
            $this->city2 = $row['city2'];
            $this->county2 = $row['county2'];
            $this->postcode2 = $row['postcode2'];
            $this->country2 = $row['country2'];
            $this->email2 = $row['email2'];
            $this->phone2 = $row['phone2'];
            $this->statusID = $row['statusID'];
            $this->expirydate = $row['expirydate'];
            $this->joindate = $row['joindate'];
            $this->updatedate = $row['updatedate'];
            $this->deletedate = $row['deletedate'];
            $this->repeatpayment = $row['repeatpayment'];
            $this->recurringpayment = $row['recurringpayment'];
            $this->username = $row['username'];
            $this->gdpr_email = $row['gdpr_email'];
            $this->gdpr_tel = $row['gdpr_tel'];
            $this->gdpr_address = $row['gdpr_address'];
            $this->gdpr_sm = $row['gdpr_sm'];            
        }
    }

    // Delete one member from the database
    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE idmember = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

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
}
?>