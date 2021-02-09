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
                                    AND DATE_SUB(NOW(), INTERVAL 12 MONTH) < `t`.`date`
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
                        `last` < DATE_SUB(NOW(), INTERVAL " .
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
}
?>