<?php
class Country{
    // database conn 
    private $conn;
    // table name
    private $table_name = "country";
    private $table_id = "id";

    // object properties
    public $id;
    public $name;

    public function __construct($db){
        $this->conn = $db;
    }

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    " . $this->table_id ." as `id`, `name`
                FROM
                    " . $this->table_name . "
                ORDER BY ". $this->table_id;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
        
        public function readOne(){

            //select data for one item using PK of table
            $query = "SELECT
                        " . $this->table_id ." as `id`, `name`
                    FROM
                        " . $this->table_name . "
                        WHERE "; 

            // WHERE clause depends on parameters
            if($this->name) {
                $query .= "LOWER(name) LIKE :name '%' ";
            }            
            else {
                $query .= $this->table_id ." = :id ";
            }
            $query .= "LIMIT 0,1";
    
            // prepare query statement
            $stmt = $this->conn->prepare($query);      

            if($this->name) {
                $name = htmlspecialchars(strip_tags($this->name));
                $stmt->bindParam (":name", $name, PDO::PARAM_STR);
            }
            else {
                $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
                $stmt->bindParam (":id", $id, PDO::PARAM_INT);
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