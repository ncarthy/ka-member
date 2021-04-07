<?php

namespace Models;

use \PDO;
use DateTime;

class MemberFilter{
    // database conn 
    private $conn;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;

        $this->reset();
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

    /* This is a giant query because I cannot use views with prepared statements */
    public function execute() {

        $query = "SELECT temp.idmember, temp.expirydate,temp.joindate,temp.reminderdate,
                        temp.updatedate,temp.deletedate, temp.lasttransactiondate,
                        IFNULL(`m`.`membership_fee`,
                            `ms`.`membershipfee`) AS `membershipfee`,
                        IFNULL(GROUP_CONCAT( CONCAT(CASE
                                            WHEN `mn`.`honorific` = '' THEN ''
                                            ELSE CONCAT(`mn`.`honorific`, ' ')
                                        END,
                                        CASE
                                            WHEN `mn`.`firstname` = '' THEN ''
                                            ELSE CONCAT(`mn`.`firstname`, ' ')
                                        END,
                                        `mn`.`surname`) SEPARATOR ' & '),
                                '') AS `name`,
                        `m`.`businessname` AS `businessname`,
                        CONCAT(`m`.`note`, ' ') AS `note`,
                        CASE
                            WHEN
                                `m`.`countryID` <> 186
                                    AND `m`.`country2ID` = 186
                            THEN
                                `m`.`addressfirstline2`
                            ELSE `m`.`addressfirstline`
                        END AS `addressfirstline`,
                        CASE
                            WHEN
                                `m`.`countryID` <> 186
                                    AND `m`.`country2ID` = 186
                            THEN
                                `m`.`addresssecondline2`
                            ELSE `m`.`addresssecondline`
                        END AS `addresssecondline`,
                        CASE
                            WHEN
                                `m`.`countryID` <> 186
                                    AND `m`.`country2ID` = 186
                            THEN
                                `m`.`city2`
                            ELSE `m`.`city`
                        END AS `city`,
                        CASE
                            WHEN
                                `m`.`countryID` <> 186
                                    AND `m`.`country2ID` = 186
                            THEN
                                `m`.`postcode2`
                            ELSE `m`.`postcode`
                        END AS `postcode`,
                        CASE
                            WHEN
                                `m`.`countryID` <> 186
                                    AND `m`.`country2ID` = 186
                            THEN
                                `c2`.`name`
                            ELSE `c1`.`name`
                        END AS `country`,
                        m.gdpr_email,m.gdpr_tel,m.gdpr_address,m.gdpr_sm,
                        `m`.`membership_idmembership` AS `idmembership`,
                        `ms`.`name` AS `membershiptype`,
                        IFNULL(pt.name,'') as paymenttype, IFNULL(b.name,'') as bankaccount,
                        m.email1, m.email2, m.postonhold
                        FROM " . $this->tablename . " temp
                        INNER JOIN `member` `m` ON temp.idmember = m.idmember
                        INNER JOIN `membershipstatus` `ms` ON (`m`.`membership_idmembership` = `ms`.`idmembership`)
                        LEFT JOIN `paymenttype` `pt` ON  `temp`.`paymenttypeID` = `pt`.`paymenttypeID`
                        LEFT JOIN `bankaccount` `b` ON  `temp`.`bankaccountID` = `b`.`bankID`
                        LEFT JOIN `country` `c1` ON (`m`.`countryID` = `c1`.`id`)
                        LEFT JOIN `country` `c2` ON (`m`.`country2ID` = `c2`.`id`)
                        LEFT JOIN membername `mn` ON `m`.`idmember` = mn.member_idmember
                        GROUP BY temp.idmember
                        ;";      
        $stmt = $this->conn->prepare( $query );  

        // Show member attributes for the members in the temp table     
        $stmt->execute();
        $num = $stmt->rowCount();
        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();
        
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                extract($row);
            
                $members_item=array(
                    "id" => $idmember,
                    "idmembership" => $idmembership,
                    "membershiptype" => $membershiptype,
                    "name" => trim($name),
                    "businessname" => $businessname,
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
                    "paymenttype" => $paymenttype,
                    "bankaccount" => $bankaccount,
                    "lasttransactiondate" => $lasttransactiondate,
                    "email" => $email1,
                    "postonhold" => $postonhold?true:false,
                );
        
                // create un-keyed list
                array_push ($members_arr["records"], $members_item);

            }   
        }
        
        return $members_arr;
    }

    /*
            NB: All the 'set' methods work by subtraction

            The filter works to remove memberids from the temporary table

    */

    public function setSurname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname NOT LIKE :param
                    ;";
        $this->executeDeleteStringParam($surname, $query);        
    }
    public function setNotSurname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname LIKE :param
                    ;";
        $this->executeDeleteStringParam($surname, $query);      
    }

    public function setBusinessname($businessname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member M2 ON M.idmember = M2.idmember
                    WHERE M2.businessname NOT LIKE :param
                    ;";
        $this->executeDeleteStringParam($businessname, $query);        
    }

    public function setBusinessOrSurname($name){    
        $query = " DELETE M
        FROM " . $this->tablename . " M
        JOIN member m ON M.idmember = m.idmember
        LEFT JOIN membername mn ON m.idmember = mn.member_idmember
        WHERE m.businessname NOT LIKE :param1 AND 
            (mn.surname NOT LIKE :param2 OR mn.surname IS NULL);";

        $stmt = $this->conn->prepare($query);      
        $param_clean = htmlspecialchars(strip_tags($name)).'%';
        $stmt->bindParam (":param1", $param_clean, PDO::PARAM_STR);
        $stmt->bindParam (":param2", $param_clean, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function setMemberTypeID($membertypeID){      
        $query = " DELETE
                    FROM " . $this->tablename . "
                    WHERE idmembership != :param
                    ;";
        $this->executeDeleteIntParam($membertypeID, $query);    
    }

    public function setMemberTypeRange($membertypeRange){      
        $query = " DELETE
                    FROM " . $this->tablename . "
                    WHERE idmembership NOT IN (:param1,:param2)
                    ;";
        $stmt = $this->conn->prepare($query);      
        $param_clean = htmlspecialchars(strip_tags($membertypeRange));

        $array = explode(",",$param_clean);
        $stmt->bindParam (":param1", $array[0], PDO::PARAM_INT);
        $stmt->bindParam (":param2", $array[1], PDO::PARAM_INT);
        $stmt->execute();       
    }

    public function setPrimaryCountryID($countryID){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member M2 ON M.idmember = M2.idmember
                    WHERE M2.countryID IS NULL OR M2.countryID != :param
                    ;";
        $this->executeDeleteIntParam($countryID, $query);        
    }

    public function setPrimaryAddress($address){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    JOIN member M2 ON M.idmember = M2.idmember
                    WHERE (M2.`addressfirstline` IS NULL OR M2.`addressfirstline` NOT LIKE :param1) AND (
                        M2.`addresssecondline` IS NULL OR M2.`addresssecondline` NOT LIKE :param2) AND (
                        M2.`city` IS NULL OR M2.`city` NOT LIKE :param3)
                    ;";
        $stmt = $this->conn->prepare($query);      
        $param_clean = '%'.htmlspecialchars(strip_tags($address)).'%';
        $stmt->bindParam (":param1", $param_clean, PDO::PARAM_STR);
        $stmt->bindParam (":param2", $param_clean, PDO::PARAM_STR);
        $stmt->bindParam (":param3", $param_clean, PDO::PARAM_STR);
        $stmt->execute();    
    }

    public function setPaymentTypeID($paymenttypeid){      
        $query = " DELETE 
                        FROM " . $this->tablename . "
                        WHERE paymenttypeID IS NULL OR paymenttypeID != :param
                        ;";
        $this->executeDeleteIntParam($paymenttypeid, $query);        
    }

    public function setBankAccountID($bankaccountid){      
        $query = " DELETE 
                        FROM " . $this->tablename . "
                        WHERE bankaccountid IS NULL OR bankaccountid != :param
                        ;";
        $this->executeDeleteIntParam($bankaccountid, $query);     
    }

    
    
    /** Perform the query that reduces the number of Members in temp
     * table. Uses prepared statements to minimize SQL injection.
     */
    private function executeDeleteStringParam($param, $query) {
        $stmt = $this->conn->prepare($query);      
        $param_clean = htmlspecialchars(strip_tags($param)).'%';
        $stmt->bindParam (":param", $param_clean, PDO::PARAM_STR);
        $stmt->execute();
    }

        /** Perform the query that reduces the number of Members in temp
     * table. Uses prepared statements to minimize SQL injection.
     */
    private function executeDeleteIntParam($param, $query) {
        $stmt = $this->conn->prepare($query);      
        $param_clean = htmlspecialchars(strip_tags($param));
        $stmt->bindParam (":param", $param_clean, PDO::PARAM_INT);
        $stmt->execute();
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
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();      
    }

    public function setPostOnHold($postonhold){    
        switch ($postonhold) {
            case 'n':
            case 'no':
                $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE postonhold IS NOT NULL AND postonhold != 0                    
                    ;";
                    $stmt = $this->conn->prepare($query);  
                    $stmt->execute(); 
                break;

            case 'y':
            case 'yes':
                $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE postonhold IS NULL OR postonhold = 0
                    ;";
                    $stmt = $this->conn->prepare($query);  
                    $stmt->execute(); 
                break;

            default:
                break;
        }
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
                    WHERE `" . $columnname . "` < :start OR 
                    `" . $columnname . "` > :end OR `" . $columnname . "` IS NULL;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam (":start", $start);
        $stmt->bindParam (":end", $end);
        $stmt->execute();       
    }

    /* Update filter to remove members who have been deleted */
    public function setNotDeleted(){      
        $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE deletedate IS NOT NULL
                    ;"; 
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();       
    }

    /* Update filter to remove members who have not been deleted */
    public function setDeleted(){      
        $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE deletedate IS NULL
                    ;";
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();       
    }

        /* DROP the given temporary table */
      private function dropTemporaryMemberTable($tablename){

        $query = "DROP TEMPORARY TABLE IF EXISTS `".$tablename."`;";
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();  
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
                        0 as paymenttypeID, 0 as bankaccountID, m.postonhold
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        GROUP BY m.idmember
                    );";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $query = "UPDATE `".$tablename."` M, transaction T
                        SET M.lasttransactionid = T.idtransaction,
                            M.paymenttypeID = T.paymenttypeID,
                            M.bankaccountID = T.bankID
                        WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    public function anonymize()
    {
        $username = $this->username;

        /* Remove names of all members to be anonymized */
        $query = "DELETE MN
                    FROM membername MN
                    JOIN `".$this->tablename."` M ON MN.member_idmember = M.idmember;";
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();

        /* Insert dummy names */
        $query = "INSERT INTO `membername` (`honorific`, `firstname`, `surname`, `member_idmember`) 
        SELECT '','', 'Anonymized',idmember FROM `".$this->tablename."`;";
        $stmt = $this->conn->prepare($query);  
        $stmt->execute();
        
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