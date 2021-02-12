<?php
class Members{
    // database conn 
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function activeMembersByType(){

        //select all data
        $query = "SELECT s.idmembership as statusID, s.name, count(*) as `count`, s.multiplier
                    ,ROUND(SUM(IFNULL(m.multiplier,s.multiplier))/count(*),2) as actmultiplier
                    ,FLOOR(SUM(IFNULL(m.multiplier,s.multiplier))) as contribution
                    FROM knightsb_membership.member m
                    LEFT JOIN membershipstatus s ON m.membership_idmembership = s.idmembership
                    WHERE s.idmembership NOT IN (8,9) AND m.deletedate IS NULL
                    GROUP BY s.idmembership                                        
                    ;";

        $stmt = $this->conn->prepare( $query );
        try{
            // execute query
            $stmt->execute();
        }
        catch(PDOException $exception){
            echo "Error retrieving members by type: " . $exception->getMessage();
        }
        
        return $stmt;
    }

    public function lifeAndHonorary(){

        //select all data
        $query = "SELECT m.idmember,m.idmembership, m.membershiptype,m.Name as `name`, 
                        IFNULL(m.businessname,'') as businessname, m.note as `note`,
                                `m`.`addressfirstline` AS `address1`,
                                `m`.`addresssecondline` AS `address2`,
                                `m`.`city`,
                                `m`.`postcode`,
                                m.country,
                                Count(t.idtransaction) as count
                        FROM vwMember m
                        LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember 
                                    AND DATE_SUB(CURDATE(), INTERVAL 12 MONTH) < `t`.`date`
                        WHERE m.idmembership IN (5,6) AND m.deletedate IS NULL
                        GROUP BY m.idmember
                        ORDER BY membershiptype                                      
                    ";

        $stmt = $this->conn->prepare( $query );
        try{
            // execute query
            $stmt->execute();
        }
        catch(PDOException $exception){
            echo "Error occurred retrieving lift/hon members.\nError message:" . $exception->getMessage();
        }
        
        return $stmt;
    }

    public function lapsedMembers($months){

        //select all data
        $query = "SELECT m.idmember, m.membershiptype,m.Name, 
                        IFNULL(m.businessname,'') as BusinessName, m.Note,
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
        try{
            // execute query
            $stmt->execute();
        }
        catch(PDOException $exception){
            echo "Error occurred retrieving lapsed members.\nError message:" . $exception->getMessage();
        }
        
        return $stmt;
    }

    public function membersPayingTwice($start, $end){       
        $tablename = '_Duplicated_'. substr(md5(microtime()),rand(0,26),5);   // 5 random characters     
        
        $query = "SELECT t.idmember,membershiptype,Name,businessname, 
                        CASE WHEN idtransaction = first_transaction THEN membershipfee ELSE 0 END as membershipfee,
                        amount,`date` 
                        FROM vwTransaction t
                        JOIN ".$tablename." d ON t.idmember = d.idmember
                        WHERE `date` >=  '" . $start ."'
                        AND `date` <= '" . $end . "'                  
                        ORDER BY t.idmember,`date`;";      
        $stmt = $this->conn->prepare( $query );  

        try{

            $this->dropTemporaryTransactionTable($tablename); // Clear any old table

            // Get transaction data for the time period
            $this->populateTemporaryDuplicateTable($tablename, $start, $end);

            // narrow down the data according to criteria          
            $stmt->execute();

            $this->dropTemporaryTransactionTable($tablename);// DROP the temp table
        }
        catch(PDOException $exception){
            echo "Error occurred retrieving duplicate transaction data.\nError message:" . $exception->getMessage();
        }
        
        return $stmt;        
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
        
        $query = "SELECT * FROM ".$tablename." WHERE ".$column." = 1 ORDER BY `date`;";      
        $stmt = $this->conn->prepare( $query );  

        try{

            $this->dropTemporaryTransactionTable($tablename); // Clear any old table

            // Get transaction data for the time period
            $this->populateTemporaryTransactionTable($tablename, $start, $end);

            // narrow down the data according to criteria          
            $stmt->execute();

            $this->dropTemporaryTransactionTable($tablename);// DROP the temp table
        }
        catch(PDOException $exception){
            echo "Error occurred retrieving transaction data.\nError message:" . $exception->getMessage();
        }
        
        return $stmt;
    }

    /* SELECT INTO a a temporary table a list of all transaciotns between the start and end dates */
    /* 'GROUP' to sum duplicates into one amount */
    private function populateTemporaryTransactionTable($tablename, $start, $end){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$tablename." AS (      
                        SELECT idmember,membershiptype,Name,businessname, membershipfee,
                            SUM(amount) as amount, Max(`date`) as `date`,
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
}
?>