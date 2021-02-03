<?php
class MembershipStatus{
    // database conn 
    private $conn;
    // table name
    private $table_name = "membershipstatus";

    // object properties
    public $id;
    public $name;

    public function __construct($db){
        $this->conn = $db;
    }

    // used by select drop-down list
    public function readAll(){
        $query = "SELECT idmembership as `id`,`name`
                FROM
                    " . $this->table_name . "
                ORDER BY idmembership";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    idmembership as `id`, `name`
                FROM
                    " . $this->table_name . "
                ORDER BY `idmembership`";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
        
        public function readOne(){

            //select data for one shop
            $query = "SELECT
                        idmembership as `id`, `name`
                    FROM
                        " . $this->table_name . "
                        WHERE "; 

            // WHERE clause depends on parameters
            if($this->name) {
                $query .= "LOWER(name) LIKE :statusname '%' ";
            }            
            else {
                $query .= "idmembership = :statusid ";
            }
            $query .= "LIMIT 0,1";
    
            // prepare query statement
            $stmt = $this->conn->prepare($query);      

            if($this->name) {
                $statusname = htmlspecialchars(strip_tags($this->name));
                $stmt->bindParam (":statusname", $statusname, PDO::PARAM_STR);
            }
            else {
                $statusid = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
                $stmt->bindParam (":statusid", $statusid, PDO::PARAM_INT);
            }
            
            $stmt->execute();

            // get retrieved row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            return $stmt;
        }
}
?>