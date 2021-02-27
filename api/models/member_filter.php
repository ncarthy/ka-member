<?php
class MemberFilter{
    // database conn 
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    private $tablename = '_Members';


    /* rest the filter for this user */
    public function reset(){

        $this->dropTemporaryMemberTable($this->tablename);

        $this->createTemporaryMemberTable($this->tablename);        
    }


    /* This method relies on a temporary table having already been created (in 
    createTemporaryMemberTable) with the IDs of all the members in the database.
    This list of all memberIDs is then whittled down by repeated application of filters
    until only the required members are left. Then a new join is done with this reduced 
    list and the results resturned to the app. */

    /* For debugging run 'tail -f /var/log/mysql/mysql.log' ON the database  and review output*/

    /* Can also use the Filter.sql file in the config directory that can help to debug SQL */
    public function execute() {

        $query = "SELECT m.idmember, m.expirydate,m.joindate,m.reminderdate,m.updatedate,m.deletedate,
                        v.`Name` as `name`, v.businessname, v.Note as `note`, 
                        addressfirstline,addresssecondline,city,postcode,country,
                        gdpr_email,gdpr_tel,gdpr_address,gdpr_sm,
                        v.idmembership, v.membershiptype,
                        t.paymentmethod, m.lasttransactiondate,
                        v.email1, v.email2
                        FROM " . $this->tablename . " m
                        JOIN vwMember v ON m.idmember = v.idmember
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                            AND m.lasttransactionid = t.`idtransaction`;";      
        $stmt = $this->conn->prepare( $query );  

        // Show member attributes for the members in the temp table     
        $stmt->execute();
        
        return $stmt;
    }

    /*
            NB: All the 'set' methods work by subtraction

            The filter works to remove memberids from the temporary table

    */

    public function setSurname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname NOT LIKE '".$surname."%'
                    ;";
        $this->conn->query($query);        
    }
    public function setNotSurname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname LIKE '".$surname."%'
                    ;";
        $this->conn->query($query);        
    }

    public function setBusinessname($businessname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member M2 ON M.idmember = M2.idmember
                    WHERE M2.businessname NOT LIKE '".$businessname."%'
                    ;";
        $this->conn->query($query);        
    }

    public function setBusinessOrSurname($name){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member m ON M.idmember = m.idmember
                    LEFT JOIN membername mn ON m.idmember = mn.member_idmember
                    WHERE m.businessname NOT LIKE '".$name."%' AND 
                        (mn.surname NOT LIKE '".$name."%' OR mn.surname IS NULL)
                    ;";
        $this->conn->query($query);        
    }

    public function setMemberTypeID($membertypeID){      
        $query = " DELETE
                    FROM " . $this->tablename . "
                    WHERE idmembership != ".$membertypeID."
                    ;";
        $this->conn->query($query);        
    }

    public function setAddress($addressfirstline){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member M2 ON M.idmember = M2.idmember
                    WHERE M2.`addressfirstline` IS NULL OR M2.`addressfirstline` NOT LIKE '%".$addressfirstline."%'
                    ;";
        $this->conn->query($query);        
    }

    public function setPaymentMethod($paymentmethod){      
        $query = " DELETE 
                        FROM " . $this->tablename . "
                        WHERE paymentmethod IS NULL OR 
                            paymentmethod = '                     ' OR 
                            paymentmethod NOT LIKE '".$paymentmethod."%'
                        ;";
        $this->conn->query($query);        
    }

    public function setEmail1($email1){      
        if(($email1 || $email1 = 'y') && $email1 != 'n') {
            $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member m1 ON M.idmember = m1.idmember
                    WHERE email1 IS NULL OR email1 = ''
                    ;";
        } else {
            $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member m1 ON M.idmember = m1.idmember
                    WHERE email1 IS NOT NULL AND email1 != ''
                    ;";
        }
        $this->conn->query($query);        
    }

    public function setExpiryRange($start, $end){      
        $this->setDateRange('expirydate',$start,$end);
    }
    public function setJoinRange($start, $end){      
        $this->setDateRange('joindate',$start,$end);
    }
    public function setDeleteRange($start, $end){      
        $this->setDateRange('deletedate',$start,$end);
    }
    public function setUpdateRange($start, $end){      
        $this->setDateRange('updatedate',$start,$end);
    }
    public function setReminderRange($start, $end){      
        $this->setDateRange('reminderdate',$start,$end);
    }
    public function setLastTransactionRange($start, $end){      
        $this->setDateRange('lasttransactiondate',$start,$end);
    }

    private function setDateRange($columnname, $start, $end){      
        $query = " DELETE
                    FROM " . $this->tablename . "
                    WHERE `" . $columnname . "` < '".$start."' OR 
                    `" . $columnname . "` > '".$end."' OR `" . $columnname . "` IS NULL;";
        $this->conn->query($query);        
    }

    /* Update filter to remove members who have been deleted */
    public function setNotDeleted(){      
        $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE deletedate IS NOT NULL
                    ;"; 
        $this->conn->query($query);        
    }

    /* Update filter to remove members who have not been deleted */
    public function setDeleted(){      
        $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE deletedate IS NULL
                    ;";
        $this->conn->query($query);        
    }

        /* DROP the given temporary table */
      private function dropTemporaryMemberTable($tablename){

        $query = "DROP TEMPORARY TABLE IF EXISTS `".$tablename."`;";
        $this->conn->query($query);
    }

    /* Create a list of ALL members in the database and with following additional columns:
        Member ID: the `idmember`PK frommember table
        Dates: deletedate & joindate & expirydate & reminderdate & updatedate
               also lasttransactiondate (if any)
        Membership type ID and type name (Individual, Lifetime etc.)

    This list of all memberIDs is then whittled down by application of filters until only
    the required members are left. Then a new join is done with this reduced list and the 
    results resturned to the app. */
    private function createTemporaryMemberTable($tablename){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS `".$tablename."` ENGINE=MEMORY AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership,
                        MAX(`date`) as lasttransactiondate, 0 as lasttransactionid,
                        '                     ' as paymentmethod
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        GROUP BY m.idmember
                    );";
        $this->conn->query($query);

        $query = "UPDATE `".$tablename."` M, transaction T
                        SET M.lasttransactionid = T.idtransaction,
                            M.paymentmethod = LEFT(T.paymentmethod,20)
                        WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;";

        $this->conn->query($query);
    }

    public function anonymize($username)
    {
        /* Remove names of all members to be anonymized */
        $query = "DELETE MN
                    FROM membername MN
                    JOIN `".$this->tablename."` M ON MN.member_idmember = M.idmember;";
        $this->conn->query($query);

        /* Insert dummy names */
        $query = "INSERT INTO `membername` (`honorific`, `firstname`, `surname`, `member_idmember`) 
        SELECT '','', 'Anonymized',idmember FROM `".$this->tablename."`;";
        $this->conn->query($query);

        
        $query = "UPDATE `member` M,
                    `" . $this->tablename . "` FM
                    SET 
                    M.note='',
                    M.addressfirstline='', 
                    M.addresssecondline='', 
                    M.city='', 
                    M.county='', 
                    M.postcode='', 
                    M.countryID=NULL, 
                    M.area='', 
                    M.email1='', 
                    M.phone1='', 
                    M.addressfirstline2='', 
                    M.addresssecondline2='', 
                    M.city2='', 
                    M.county2='', 
                    M.postcode2='', 
                    M.country2ID=NULL, 
                    M.email2='', 
                    M.phone2='', 
                    M.updatedate= NULL, 
                    M.username=:username                  
                 WHERE
                    M.idmember=FM.idmember
                ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $username=htmlspecialchars(strip_tags($username));

        // bind values
        $stmt->bindParam(":username", $username);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    // Given two strings that represent dates (but one of them may be empty/null/unset)
    public function sanitizeDateValues($startdate, $enddate)
    {
        $end = date('Y-m-d');

        if(empty($startdate) && empty($enddate)) {
            // default values are the period 1 year back from today            
            $start = (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
            return array($start, $end); 
        } else if (empty($startdate)) {
            if ($this->validateDate($enddate)) {
                $start = (new DateTime($enddate))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
                return array($start, $enddate); 
            }
            else {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Enddate is in the wrong format.")
                );
                exit(1);
            }
        } else if (empty($enddate)) {
            if ($this->validateDate($startdate)) {
                return array($startdate, $end); 
            }
            else {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            }
        } else {
            if (!$this->validateDate($startdate)) {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            } else if (!$this->validateDate($enddate)) {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Enddate is in the wrong format.")
                );
                exit(1);
            }
            return array($startdate, $enddate);
        }

    }

    private function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
?>