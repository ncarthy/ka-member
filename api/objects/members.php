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
}
?>