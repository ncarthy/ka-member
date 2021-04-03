<?php

namespace Models;

use \PDO;

class Members{
    // database conn 
    private $conn;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    public function lapsedMembers($months){

        //select all data
        $query = "SELECT m.idmember,m.membership_idmembership as membershiptypeid, 
                        ms.name as membershiptype,
                        m.note,
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
                        IFNULL(`m`.`businessname`,'') AS `businessname`,
                        CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                            THEN `m`.`addressfirstline2`
                            ELSE `m`.`addressfirstline`
                        END AS `addressfirstline`,
                        CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                            THEN `m`.`addresssecondline2`
                            ELSE `m`.`addresssecondline`
                        END AS `addresssecondline`,
                        CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                            THEN `m`.`city2`
                            ELSE `m`.`city`
                        END AS `city`,
                        CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                            THEN
                                `m`.`postcode2`
                            ELSE `m`.`postcode`
                        END AS `postcode`,
                                IFNULL(CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                                            THEN `c2`.`name`
                                            ELSE `c1`.`name`
                                        END,'') AS `country`,
                        m.updatedate, m.expirydate,  
                        m.reminderdate,
                        `count`, 
                        `lasttransactiondate`
                    FROM member m
                    INNER JOIN membershipstatus ms ON m.membership_idmembership = ms.idmembership
                    INNER JOIN membername mn ON m.idmember = mn.member_idmember
                    LEFT JOIN `country` `c1` ON `m`.`countryID` = `c1`.`id`
                    LEFT JOIN `country` `c2` ON `m`.`country2ID` = `c2`.`id`
                    LEFT OUTER JOIN (SELECT member_idmember, COUNT(idtransaction) as `count`, 
                        MAX(`date`) AS `lasttransactiondate` FROM `transaction` GROUP BY member_idmember) as t ON m.idmember = t.member_idmember
                    WHERE m.membership_idmembership IN (2,3,4,10) AND m.deletedate IS NULL
                    GROUP BY m.idmember
                    HAVING `lasttransactiondate` IS NULL OR 
                        `lasttransactiondate` < DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                    ORDER BY `lasttransactiondate`                                     
                    ";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam (":months", $months);
        $stmt->execute();
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of lifetime members
        $members_arr["records"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $member = $this->extractMember($row);
                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }
        
        return $members_arr;

    }

    public function membersPayingTwice($start, $end){ 
        return $this->tabulateTransactionData('Duplicates', $start, $end);
    }

    public function contributingExMembers($start, $end){       
        return $this->tabulateTransactionData('CEM', $start, $end);
    }

    public function discountMembers($start, $end){       
        return $this->tabulateTransactionData('Discount', $start, $end);
    }

    public function payingHonLifeMembers($start, $end){       
        return $this->tabulateTransactionData('HonLife', $start, $end);
    }

    public function lapsedCEM($start, $end, $cutoff){       
        return $this->tabulateTransactionData('LapsedCEM', $start, $end, $cutoff);
    }

    public function formerMembersStillPaying($start, $end){       
        return $this->tabulateTransactionData('Former', $start, $end);
    }

    private function tabulateTransactionData($report_name, $start, $end, $cutoff=''){

        $tablename = '_Transactions_'. substr(md5(microtime()),rand(0,26),5);   // 5 random characters     
                        
        $this->dropTemporaryTransactionTable($tablename); // Clear any old table

        // Get transaction data for the time period
        $this->populateTemporaryTransactionTable($tablename, $start, $end);

        // narrow down the data according to criteria        
        $query = "SELECT idmember,membershiptypeid,membershiptype,note,name,businessname,addressfirstline,
                        addresssecondline,city,postcode,country,updatedate, expirydate, reminderdate, membershipfee,
                        t.`amount`, t.`lasttransactiondate`,t.`count`
                    FROM vwMember m
                    JOIN ".$tablename." t ON m.idmember = t.member_idmember";   

        switch ($report_name) {
            case 'HonLife':
                $query = $query. " WHERE amount>0 AND membershiptypeid IN (5,6) ORDER BY `lasttransactiondate`;";
                break;
            case 'CEM':
                $query = $query. " WHERE amount>=0 AND membershiptypeid=8 ORDER BY `lasttransactiondate`;";
                break;
            case 'Discount':
                $query = $query. " WHERE amount>=0 AND amount < membershipfee AND membershiptypeid NOT IN(5,6,8) ORDER BY `lasttransactiondate`;";
                break;
            case 'Duplicates':
                $query = $query. " WHERE amount>=0 AND count > 1 ORDER BY `lasttransactiondate`;";
                break;
            case 'LapsedCEM':
                $query = $query. " WHERE amount>=0 AND membershiptypeid=8 AND `lasttransactiondate` < '" . $cutoff . "' ORDER BY `lasttransactiondate`;";
                break;                
            case 'Former':
                $query = $query. " WHERE amount>=0 AND membershiptypeid=9 ORDER BY `lasttransactiondate`;";
                break;
        }

        $stmt = $this->conn->query($query); 
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["start"] = $start;
        $members_arr["end"] = $end;
        $members_arr["total"] = 0;  // total amount of fees received
        $members_arr["expected"] = 0; // expected amount of fees
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        $total_received = 0; // sum of member payments as we loop over rows
        $total_expected = 0; // sum of member fees as we loop over rows

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                $member = $this->extractMember($row);

                $total_received+=$row['amount'];
                $total_expected+=$row['membershipfee'];

                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }
        $members_arr["total"] = $total_received;
        // honorary and life members aren't expected to pay anything
        $members_arr["expected"] = $report_name=='HonLife'?0:$total_expected; 

        $this->dropTemporaryTransactionTable($tablename);// DROP the temp table

        return $members_arr;

    }

    /* SELECT INTO a a temporary table a list of all transaciotns between the start and end dates */
    /* 'GROUP' to sum duplicates into one amount */
    private function populateTemporaryTransactionTable($tablename, $start, $end){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$tablename." AS (      
                    SELECT member_idmember, 
                        COUNT(idtransaction) as `count`, 
                        SUM(amount) as `amount`,
                        MAX(`date`) AS `lasttransactiondate`                     
                    FROM `transaction` 
                    WHERE `date` >=  :start AND `date` <= :end 
                    GROUP BY member_idmember
                    );";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam (":start", $start);
        $stmt->bindParam (":end", $end);
        $stmt->execute();
    }

        /* DROP the given temporary table */
      private function dropTemporaryTransactionTable($tablename){

        $query = "DROP TEMPORARY TABLE IF EXISTS ".$tablename.";";
        $stmt = $this->conn->query($query);
    }

    public function mailingList(){

        //select all data
        $query = "SELECT m.`idmember`, `name`, v.`title`, v.`businessname`, v.`addressfirstline`, v.`addresssecondline`,
        v.`city`, v.`postcode`, `country`,
                CASE WHEN `m`.`countryID` <> 186 AND `m`.`country2ID` = 186
                    THEN `m`.`country2ID`
                    ELSE `m`.`countryID`
                END AS `countryID`
                FROM `vwMember` v
                JOIN member m ON v.idmember = m.idmember
                WHERE `m`.postonhold = 0 AND 							# Not post on hold
                    `m`.membership_idmembership NOT IN (7,8,9) AND      # Active member
                    IFNULL(m.addressfirstline,'') != ''					# Valid Address
                AND (countryID = 186 OR country2ID = 186)				# UK only
                ORDER BY postcode;";

        $stmt = $this->conn->query( $query );
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num;
        $members_arr["records"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $member=array(
                    "id" => $idmember,
                    "name" => $name,
                    "title" => $title,
                    "businessname" => $businessname,
                    "addressfirstline" => $addressfirstline,
                    "addresssecondline" => $addresssecondline,
                    "city" => $city,
                    "postcode" => $postcode,
                    "countryID" => $countryID
                );
                
                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }
        
        return $members_arr;

    }

    public function emailList(){

        //select all data
        $query = "SELECT email1 as email
                    FROM member
                    WHERE membership_idmembership NOT IN (7,8,9) AND email1 IS NOT NULL AND email1 != '' AND
                        email1 REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$'
                    UNION
                    SELECT email2
                    FROM member
                    WHERE membership_idmembership NOT IN (7,8,9) AND email2 IS NOT NULL AND email2 != '' AND
                        email2 REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$'
                    ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num;
        $members_arr["records"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                
                // create un-keyed list
                array_push ($members_arr["records"], $email);
            }
        }
        
        return $members_arr;

    }

    public function noUKAddress(){

        //select all data
        $query = "SELECT 
                        m.idmember,membershiptypeid,membershiptype,note,`name`,businessname,addressfirstline,
                        addresssecondline,city,postcode,country,updatedate, expirydate, reminderdate, membershipfee,
                        t.`amount`, t.`lasttransactiondate`,t.`count` 
                    FROM vwMember m
                    LEFT OUTER JOIN (SELECT member_idmember, COUNT(idtransaction) as `count`, SUM(amount) as `amount`,
                        MAX(`date`) AS `lasttransactiondate` FROM `transaction` GROUP BY member_idmember) as t ON m.idmember = t.member_idmember
                    WHERE deletedate IS NULL AND (country != 'United Kingdom' OR addressfirstline = '');";

        $stmt = $this->conn->query( $query );
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                $member = $this->extractMember($row);

                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }

        return $members_arr;

    }

    public function invalidEmails(){

        //select all data
        $query = "SELECT idmember,membershiptype,`name`,businessname,email1 as email1,'' as email2 
                        FROM `vwMember` 
                        WHERE email1 IS NOT NULL AND email1 != '' AND 
                            `email1` NOT REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$'
                    UNION
                        SELECT idmember,membershiptype,`name`,businessname,'' as email1, email2 
                        FROM `vwMember` 
                        WHERE email2 IS NOT NULL AND email2 != '' AND 
                        `email2` NOT REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$';";

        $stmt = $this->conn->query( $query );
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $member=array(
                    "id" => $idmember,
                    "membershiptype" => $membershiptype,
                    "name" => $name,
                    "businessname" => $businessname,
                    "email1" => $email1,
                    "email2" => $email2,
                );
                
                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }

        return $members_arr;

    }

    public function invalidPostcodes(){

        //select all data
        $query = "SELECT m.idmember,membershiptype,`name`,m.businessname,m1.postcode as postcode1,'' as postcode2
                        FROM `vwMember` m
                        JOIN member m1 ON m.idmember = m1.idmember
                        WHERE m1.deletedate IS NULL AND m1.countryID=186 AND m1.postcode IS NOT NULL AND m1.postcode != '' AND 
                            m1.postcode NOT REGEXP '^([A-Z][A-HJ-Y]?[0-9][A-Z0-9]? ?[0-9][A-Z]{2}|GIR ?0A{2})$'
                    UNION
                        SELECT m.idmember,membershiptype,`name`,m.businessname,'' as postcode1, m1.postcode2
                        FROM `vwMember` m
                        JOIN member m1 ON m.idmember = m1.idmember
                        WHERE m1.deletedate IS NULL AND m1.countryID=186 AND m1.postcode2 IS NOT NULL AND m1.postcode2 != '' AND 
                        m1.postcode2 NOT REGEXP '^([A-Z][A-HJ-Y]?[0-9][A-Z0-9]? ?[0-9][A-Z]{2}|GIR ?0A{2})$';";

        $stmt = $this->conn->query( $query );
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $member=array(
                    "id" => $idmember,
                    "membershiptype" => $membershiptype,
                    "name" => $name,
                    "businessname" => $businessname,
                    "postcode1" => $postcode1,
                    "postcode2" => $postcode2,
                );
                
                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }

        return $members_arr;

    }

    public function deletedButNotFormer(){

        $query = "SELECT 
                        m.idmember,membershiptypeid,membershiptype,note,`name`,businessname,addressfirstline,
                        addresssecondline,city,postcode,country,updatedate, expirydate, reminderdate, membershipfee,
                        t.`amount`, t.`lasttransactiondate`,t.`count` 
                    FROM vwMember m
                    LEFT OUTER JOIN (SELECT member_idmember, COUNT(idtransaction) as `count`, SUM(amount) as `amount`,
                        MAX(`date`) AS `lasttransactiondate` FROM `transaction` GROUP BY member_idmember) as t ON m.idmember = t.member_idmember
                    WHERE deletedate IS NOT NULL AND membershiptypeid NOT IN (8,9);";

        $stmt = $this->conn->query($query); 
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                $member = $this->extractMember($row);

                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }

        return $members_arr;

    }

    public function oldFormerMember($deletedBefore){

        $query = "SELECT 
                        m.idmember,membershiptypeid,membershiptype,note,`name`,businessname,addressfirstline,
                        addresssecondline,city,postcode,country,updatedate, expirydate, reminderdate, membershipfee,
                        t.`amount`, t.`lasttransactiondate`,t.`count`, deletedate 
                    FROM vwMember m
                    LEFT OUTER JOIN (SELECT member_idmember, COUNT(idtransaction) as `count`, SUM(amount) as `amount`,
                        MAX(`date`) AS `lasttransactiondate` FROM `transaction` GROUP BY member_idmember) as t ON m.idmember = t.member_idmember
                    WHERE deletedate IS NOT NULL AND membershiptypeid =9 AND deletedate <= '".$deletedBefore."' AND m.name != 'Anonymized';";

        $stmt = $this->conn->query($query); 
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["count"] = $num; // add the count of rows
        $members_arr["records"]=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                $member = $this->extractMember($row);

                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }

        return $members_arr;

    }

    private function extractMember($row) {
        extract($row);
            
        $member=array(
            "id" => $idmember,
            "membershiptypeid" => $membershiptypeid,
            "membershiptype" => $membershiptype,
            "name" => $name,
            "businessname" => $businessname,
            "note" => $note,
            "addressfirstline" => $addressfirstline,
            "addresssecondline" => $addresssecondline,
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country,
            "updatedate" => $updatedate,
            "expirydate" => $expirydate,
            "reminderdate" => $reminderdate,
            "count" => $count,
            "lasttransactiondate" => $lasttransactiondate
        );

        if (isset($membershipfee)) {
            $member['membershipfee'] = $membershipfee;
        }
        if (isset($amount)) {
            $member['amount'] = $amount;
        }
        if (isset($deletedate)) {
            $member['deletedate'] = $deletedate;
        }

        return $member;
    }

    /** Set all the members in the $ids list to be former members */
    public function setToFormer($ids, $username) {
        $ids_string = implode(',',$ids);
        $query = "UPDATE member m, membershipstatus ms
                        SET membership_idmembership = ms.idmembership,
                            updatedate= NULL, 
                            deletedate=CASE WHEN deletedate IS NULL THEN CURDATE() ELSE deletedate END,
                            username=:username 
                        WHERE idmember IN (".$ids_string.") AND ms.name LIKE 'former%';";    

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam (":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }

        /** Anonymize the members in the $ids list */
        public function anonymize($ids, $username) {
            $ids_string = implode(',',$ids);

            $query = "DELETE FROM `membername` WHERE member_idmember IN (".$ids_string.");";
            $stmt = $this->conn->query( $query );

            /* Insert dummy name */
            $query = "INSERT INTO `membername` 
                ( `honorific`, `firstname`, `surname`, `member_idmember`) 
                    SELECT '','','Anonymized',idmember
                    FROM member WHERE idmember IN (".$ids_string.");";
            $stmt = $this->conn->query($query);

            $query = "UPDATE member
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
                        WHERE idmember IN (".$ids_string.");";    
    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam (":username", $username, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount();
        }
}
?>