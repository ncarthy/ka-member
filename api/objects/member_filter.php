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

    public function execute() {

        $query = "SELECT m.idmember, m.expirydate,m.joindate,m.reminderdate,m.updatedate,m.deletedate,
                        v.`Name` as `name`, v.businessname, v.Note as `note`, 
                        addressfirstline,addresssecondline,city,postcode,country,
                        gdpr_email,gdpr_tel,gdpr_address,gdpr_sm,
                        v.idmembership, v.membershiptype
                        FROM " . $this->tablename . " m
                        JOIN vwMember v ON m.idmember = v.idmember;";      
        $stmt = $this->conn->prepare( $query );  

        // narrow down the data according to criteria          
        $stmt->execute();
        
        return $stmt;
    }


    public function surname($surname){      
        $query = " DELETE M
                    FROM " . $this->tablename . " M
                    LEFT JOIN membername MN ON M.idmember = MN.member_idmember
                    WHERE MN.member_idmember IS NULL OR MN.surname NOT LIKE '".$surname."%'
                    ;";
        $this->conn->query($query);        
    }

    public function notDeleted(){      
        $query = " DELETE 
                    FROM " . $this->tablename . "
                    WHERE deletedate IS NOT NULL
                    ;";
        $this->conn->query($query);        
    }

    public function deleted(){      
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

    private function createTemporaryMemberTable($tablename){

        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS `".$tablename."` AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership
                        FROM member
                    );";
        $this->conn->query($query);
    }

}
?>