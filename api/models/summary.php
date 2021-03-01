<?php

namespace Models;

use \PDO;

class MembersSummary{
    // database conn 
    private $conn;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
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

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $members_arr=array();
        $members_arr["total"] = 0;
        $members_arr["records"]=array();

        $contribution_total =0; // sum of member contribution as we loop over rows

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $members_item=array(
                    "id" => $statusID,
                    "name" => $name,
                    "count" => $count,
                    "multiplier" => $multiplier,
                    "actmultiplier" => $actmultiplier,
                    "contribution" => $contribution
                );

                $contribution_total+=$contribution;

                // create un-keyed list
                array_push ($members_arr["records"], $members_item);
            }

            $members_arr["total"] = $contribution_total; // add a contribution_total field  
        }

        return $members_arr;
    }


}
?>