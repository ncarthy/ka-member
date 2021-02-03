<?php
class User{
    // database conn 
    private $conn;
    // table name
    private $table_name = "user";

    public function __construct($db){
        $this->conn = $db;
    }

    // object properties
    public $id;
    public $username;
    public $firstname;
    public $surname;
    public $shopid;
    public $isadmin;
    public $suspended;
    public $password;


    // used by select drop-down list
    public function readAll(){

        //select all data
        $query = "SELECT
                    u.id, u.`username`, u.`password`, u.`firstname`, u.`surname`,
                    u.`shopid`, s.`name` as shopname, u.isAdmin, u.suspended
                    FROM
                    " . $this->table_name . " u
                    LEFT JOIN shop s ON u.shopid = s.id ";

        $stmt = $this->conn->prepare( $query );
        try{
            // execute query
            $stmt->execute();
        }
        catch(PDOException $exception){
            echo "Error retrieving users: " . $exception->getMessage();
        }
        
        return $stmt;
    }

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    shopid=:shopid,
                    firstname=:firstname, 
                    surname=:surname,
                    isAdmin=:isadmin, 
                    suspended=:suspended ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->shopid=htmlspecialchars(strip_tags($this->shopid));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->isadmin=htmlspecialchars(strip_tags($this->isadmin));
        $this->suspended=htmlspecialchars(strip_tags($this->suspended));

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":shopid", $this->shopid);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":isadmin", $this->isadmin);
        $stmt->bindParam(":suspended", $this->suspended);
        

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    shopid=:shopid,
                    firstname=:firstname, 
                    surname=:surname,
                    isAdmin=:isadmin, 
                    suspended=:suspended
                    " . (isset($this->password)?',password=:password ':'') ."
                 WHERE
                    id=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->shopid=htmlspecialchars(strip_tags($this->shopid));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->isadmin=htmlspecialchars(strip_tags($this->isadmin));
        $this->suspended=htmlspecialchars(strip_tags($this->suspended));
        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":shopid", $this->shopid);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":isadmin", $this->isadmin);
        $stmt->bindParam(":suspended", $this->suspended);
        

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    // find the details of one user
    public function readOne(){

        //select all data
        $query = "SELECT
                    u.id, u.`username`, u.`password`, u.`firstname`, u.`surname`,
                    u.`shopid`, s.`name` as shopname, u.isAdmin, u.suspended
                    FROM
                    " . $this->table_name . " u
                    LEFT JOIN shop s ON u.shopid = s.id
                    WHERE u.id = ?
                    LIMIT 0,1";
                
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(1, $this->id);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        $this->id = $row['id'];
        $this->username = $row['username'];
        $this->password = $row['password'];
        $this->shopid = $row['shopid'];
        $this->firstname = $row['firstname'];
        $this->surname = $row['surname'];
        $this->isadmin = $row['isAdmin'];
        $this->suspended = $row['suspended'];
    }

        // find the details of one user
        public function readOneRaw($username){

            //select all data
            $query = "SELECT
                        u.id, u.`username`, u.`password`, u.`firstname`, u.`surname`,
                        u.`shopid`, s.`name` as shopname, u.isAdmin, u.suspended
                        FROM
                        " . $this->table_name . " u
                        LEFT JOIN shop s ON u.shopid = s.id
                        WHERE username = ?
                        LIMIT 0,1";
                    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $username);
            $stmt->execute();
    
            return $stmt;
        }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // used for paging products
    public function count(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
    
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_rows'];
    }
}
?>