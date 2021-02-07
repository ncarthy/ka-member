<?php
class MemberName{
    // database conn 
    private $conn;
    // table name
    private $table_name = "membername";
    private $table_id = "idmembername";

    // object properties
    public $id;
    public $honorific;
    public $firstname;
    public $surname;
    public $idmember;

    public function __construct($db){
        $this->conn = $db;
    }

 
    public function readOne(){

        //select data for one shop
        $query = "SELECT
                    " . $this->table_id ." as `id`, `honorific`, 
                    `firstname`, `surname`, `member_idmember`
                FROM
                    " . $this->table_name . "
                    WHERE "; 

        // WHERE clause depends on parameters
        $query .= $this->table_id ." = :id ";
        $query .= "LIMIT 0,1";

        // prepare query statement
        $stmt = $this->conn->prepare($query);      

        if($this->idmember) {
            $idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":idmember", $idmember, PDO::PARAM_INT);
        }
        else {
            $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":id", $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // set values to object properties
            $this->id = $row['id'];
            $this->honorific = $row['honorific'];
            $this->firstname = $row['firstname'];
            $this->surname = $row['surname'];
            $this->idmember = $row['member_idmember'];
            return $stmt;
        }
    }

    public function readMemberNames(){

        //select data for one shop
        $query = "SELECT
                    " . $this->table_id ." as `id`, `honorific`, 
                    `firstname`, `surname`, `member_idmember` as `idmember`
                FROM
                    " . $this->table_name . "
                    WHERE "; 

        // WHERE clause depends on parameters
        $query .= "member_idmember = :idmember ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);      

        $idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam (":idmember", $idmember, PDO::PARAM_INT);
        
        $stmt->execute();

        return $stmt;
    }

    function deleteNamesForMember(){
        $query = "DELETE FROM " . $this->table_name . " WHERE member_idmember = ?";

        $stmt = $this->conn->prepare($query);
        $this->idmember=htmlspecialchars(strip_tags($this->idmember));
        $idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $idmember);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }
}
?>