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
    public function execute() {

        $query = "SELECT m.idmember, m.expirydate,m.joindate,m.reminderdate,m.updatedate,m.deletedate,
                        v.`Name` as `name`, v.businessname, v.Note as `note`, 
                        addressfirstline,addresssecondline,city,postcode,country,
                        gdpr_email,gdpr_tel,gdpr_address,gdpr_sm,
                        v.idmembership, v.membershiptype,
                        t.paymentmethod, m.lasttransactiondate
                        FROM " . $this->tablename . " m
                        JOIN vwMember v ON m.idmember = v.idmember
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                            AND m.lasttransactionid = t.`idtransaction`;";      
        $stmt = $this->conn->prepare( $query );  

        // Show member attributes for the members in the temp table     
        $stmt->execute();
        
        return $stmt;
    }


    public function setSurname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname NOT LIKE '".$surname."%'
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

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS `".$tablename."` AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership,
                        MAX(`date`) as lasttransactiondate, 0 as lasttransactionid
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        GROUP BY m.idmember
                    );";
        $this->conn->query($query);

        $query = "UPDATE `".$tablename."` M, transaction T
                        SET M.lasttransactionid = T.idtransaction
                        WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;";

        $this->conn->query($query);
    }

}
?>