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
        $query = "SELECT m.idmember,m.idmembership, m.membershiptype,m.Name as `name`,  
                        IFNULL(m.businessname,'') as businessname, m.note as `note`,
                        `m`.`addressfirstline` AS `address1`,
                        `m`.`addresssecondline` AS `address2`,
                        `m`.`city`,
                        `m`.`postcode`,
                        m.country,
                        m.updatedate, m.expirydate,  
                        m.reminderdate,
                        COUNT(t.idtransaction) as `count`, 
                        MAX(t.`date`) AS `last`
                    FROM vwMember m
                    LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember
                    WHERE m.idmembership IN (2,3,4,10) AND m.deletedate IS NULL
                    GROUP BY m.idmember
                    HAVING `last` IS NULL OR 
                        `last` < DATE_SUB(CURDATE(), INTERVAL " .
                        $months
                        . " MONTH)
                    ORDER BY `last`                                     
                    ";

        $stmt = $this->conn->prepare( $query );
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

        $tablename = '_Duplicated_'. substr(md5(microtime()),rand(0,26),5);   // 5 random characters     
        
        $query = "SELECT 
                        d.idmember,t.idmembership, t.membershiptype,t.`name`, membershipfee,
                            IFNULL(t.businessname,'') as businessname, t.note as `note`,
                            `t`.`address1`, `t`.`address2`, `t`.`city`,
                            `t`.`postcode`, t.country, t.updatedate, t.expirydate,  
                            t.reminderdate,
                            SUM(amount) as amount, Max(`date`) as `last`,
                            COUNT(t.idtransaction) as `count`
                        FROM vwTransaction t
                        JOIN ".$tablename." d ON t.idmember = d.idmember
                        WHERE `date` >=  '" . $start ."'
                        AND `date` <= '" . $end . "'                  
                        ORDER BY t.idmember,`date`;";               

        $this->dropTemporaryTransactionTable($tablename); // Clear any old table

        // Get transaction data for the time period
        $this->populateTemporaryDuplicateTable($tablename, $start, $end);

        // narrow down the data according to criteria  
        $stmt = $this->conn->prepare( $query );         
        $stmt->execute();
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

                // create un-keyed list
                array_push ($members_arr["records"], $member);
            }
        }
        $members_arr["total"] = $total_received;
        // honorary and life members aren't expected to pay anything
        $members_arr["expected"] = $total_expected; 

        $this->dropTemporaryTransactionTable($tablename);// DROP the temp table

        return $members_arr;      
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


    private function tabulateTransactionData($column, $start, $end){

        $tablename = '_Transactions_'. substr(md5(microtime()),rand(0,26),5);   // 5 random characters     
        
        $query = "SELECT * FROM ".$tablename." WHERE ".$column." = 1 ORDER BY `last`;";               

        $this->dropTemporaryTransactionTable($tablename); // Clear any old table

        // Get transaction data for the time period
        $this->populateTemporaryTransactionTable($tablename, $start, $end);

        // narrow down the data according to criteria          
        $stmt = $this->conn->prepare( $query ); 
        $stmt->execute();
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
        $members_arr["expected"] = $column=='HonLife'?0:$total_expected; 

        $this->dropTemporaryTransactionTable($tablename);// DROP the temp table

        return $members_arr;

    }

    /* SELECT INTO a a temporary table a list of all transaciotns between the start and end dates */
    /* 'GROUP' to sum duplicates into one amount */
    private function populateTemporaryTransactionTable($tablename, $start, $end){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$tablename." AS (      
                        SELECT 
                            idmember,t.idmembership, t.membershiptype,t.`name`, membershipfee,
                            IFNULL(t.businessname,'') as businessname, t.note as `note`,
                            `t`.`address1`, `t`.`address2`, `t`.`city`,
                            `t`.`postcode`, t.country, t.updatedate, t.expirydate,  
                            t.reminderdate,
                            SUM(amount) as amount, Max(`date`) as `last`,
                            COUNT(t.idtransaction) as `count`,
                            CASE WHEN SUM(amount)>=0 AND idmembership=8 THEN 1 ELSE 0 END as `CEM`,
                            CASE WHEN SUM(amount)>=0 AND SUM(amount) < membershipfee AND idmembership NOT IN(5,6,8) THEN 1 ELSE 0 END as `Discount`,
                            CASE WHEN SUM(amount)>0 AND idmembership IN (5,6) THEN 1 ELSE 0 END as `HonLife`,
                            CASE WHEN SUM(amount) = membershipfee THEN 1 ELSE 0 END as `Correct`
                        FROM vwTransaction t
                        WHERE `date` >=  '" . $start ."'
                        AND `date` <= '" . $end . "'
                        GROUP BY idmember,membershiptype,Name,businessname
                    );";
        $this->conn->query($query);
    }

        /* DROP the given temporary table */
      private function dropTemporaryTransactionTable($tablename){

        $query = "DROP TEMPORARY TABLE IF EXISTS ".$tablename.";";
        $this->conn->query($query);
    }

        /* SELECT INTO a a temporary table a list of all idmembers between the start and end dates for members
      who have duplicate transactions */
      private function populateTemporaryDuplicateTable($tablename, $start, $end){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$tablename." AS (      
                        SELECT `member_idmember` as `idmember`, Min(idtransaction) as first_transaction
                        FROM `transaction` t
                        WHERE `date` >=  '" . $start ."'
                        AND `date` <= '" . $end . "'
                        GROUP BY `idmember`
                        HAVING Count(*) > 1
                    );";
        $this->conn->query($query);
    }

    private function extractMember($row) {
        extract($row);
            
        $member=array(
            "id" => $idmember,
            "statusID" => $idmembership,
            "statusname" => $membershiptype,
            "name" => $name,
            "business" => $businessname,
            "note" => $note,
            "address1" => $address1,
            "address2" => $address2,
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country,
            "updatedate" => $updatedate,
            "expirydate" => $expirydate,
            "reminderdate" => $reminderdate,
            "count" => $count,
            "last" => $last
        );

        return $member;
    }
}
?>