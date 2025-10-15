<?php

namespace Models;

use \PDO;

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

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    honorific=:honorific,
                    firstname=:firstname, 
                    surname=:surname,
                    member_idmember=:idmember";

        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->honorific=htmlspecialchars(strip_tags($this->honorific));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->idmember=filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);

        if ($this->idmember <= 0) { return false; } // must be non-zero, not-negative.

        // bind values
        $stmt->bindParam(":honorific", $this->honorific);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        

        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            if($this->id) {
                return true;
            } else {
                return false;
            }
        }
            
        return false;
    }

    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    honorific=:honorific,
                    firstname=:firstname, 
                    surname=:surname
                 WHERE
                    idmembername=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $this->honorific=htmlspecialchars(strip_tags($this->honorific));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":honorific", $this->honorific);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname); 

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }
 
    public function read_by_id(){

        //select data for one item using PK of table
        $query = "SELECT
                    " . $this->table_id ." as `id`, `honorific`, 
                    `firstname`, `surname`, `member_idmember`
                FROM
                    " . $this->table_name . "
                    WHERE "; 
        $query .= $this->table_id ." = :id ";
        $query .= "LIMIT 0,1";

        // prepare query statement
        $stmt = $this->conn->prepare($query);      

        $this->id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam (":id", $this->id, PDO::PARAM_INT);
        
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
        }

        $item = array(
            "id" => $this->id,
            "honorific" => $this->honorific,
            "firstname" => $this->firstname,
            "surname" => $this->surname,
            "idmember" => $this->idmember
        );

        if (!$this->surname) {
            http_response_code(422); 
            echo json_encode(
                array("message" => "No names found with that idmembername.",
                        "idmembername" => $this->id), JSON_NUMERIC_CHECK
            );
            exit(1);
        }

        return $item;
    }

    public function read_by_idmember(){

        //select data for one item using PK of table
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
        $num = $stmt->rowCount();

        $items_arr=array();

        // check if more than 0 record found
        if($num>0){

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                    $item=array(
                        "id" => $id,
                        "honorific" => html_entity_decode($honorific??''),
                        "firstname" => html_entity_decode($firstname??''),
                        "surname" => html_entity_decode($surname??''),
                        "idmember" => $idmember
                    );

                array_push ($items_arr, $item);
            }
        }

        return $items_arr;
    }

    /* Delete all names for a member from the database by providing the idmember FK */
    function delete_by_idmember(){
        $query = "DELETE FROM " . $this->table_name . " WHERE member_idmember = ?";

        $stmt = $this->conn->prepare($query);
        $idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $idmember, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    /* Delete a single member name from the database by providing the idmembername PK */
    function delete_by_id(){
        $query = "DELETE FROM " . $this->table_name . " WHERE idmembername = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $idmembername = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $idmembername, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }
}