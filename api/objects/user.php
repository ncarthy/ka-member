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
    public $isadmin;
    public $suspended;
    public $password;
    public $fullname;
    public $failedloginattempts;

    // used by select drop-down list
    public function readAll(){

        //select all data
        $query = "SELECT
                    u.iduser as `id`, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.failedloginattempts
                    FROM
                    " . $this->table_name . " u";                    

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
                    isAdmin=:isadmin, 
                    name=:fullname,
                    suspended=:suspended
                    " . (isset($this->password)?',new_pass=:password ':'');
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->isadmin=filter_var($this->isadmin, FILTER_SANITIZE_NUMBER_INT);
        $this->suspended=filter_var($this->suspended, FILTER_SANITIZE_NUMBER_INT);
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $this->isadmin);
        $stmt->bindParam(":suspended", $this->suspended);
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":password", $this->password);
        

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
                    username=:username,
                    isAdmin=:isadmin, 
                    suspended=:suspended,
                    name=:fullname,
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',new_pass=:password ':'') ."
                 WHERE
                    iduser=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->isadmin=filter_var($this->isadmin, FILTER_SANITIZE_NUMBER_INT);
        $this->suspended=filter_var($this->suspended, FILTER_SANITIZE_NUMBER_INT);
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $this->isadmin);
        $stmt->bindParam(":suspended", $this->suspended);
        $stmt->bindParam(":fullname", $this->fullname);        
        $stmt->bindParam(":failedloginattempts", $this->failedloginattempts);     

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    // find the details of one user using $id
    public function readOne(){

        //select all data
        $query = "SELECT
                    u.`iduser` as id, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.`failedloginattempts`
                    FROM
                    " . $this->table_name . " u
                    WHERE u.iduser = ?
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
        if ( !empty($row) ) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->fullname = $row['name'];
            $this->password = $row['password'];
            $this->isadmin = $row['isAdmin'];
            $this->suspended = $row['suspended'];
            $this->failedloginattempts = $row['failedloginattempts'];
        }
    }

        // find the details of one user using $username
        public function readOneRaw($username){

            //select all data
            $query = "SELECT
                        u.`iduser` as id, u.`username`, u.`new_pass` as `password`,
                        u.isAdmin, u.suspended, u.`name`, u.`failedloginattempts`
                        FROM
                        " . $this->table_name . " u
                        WHERE username = ?
                        LIMIT 0,1";
                    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $username);
            $stmt->execute();
    
            return $stmt;
        }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE iduser = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    function updateFailedAttempts($id, $failedloginattempts, bool $suspended){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    failedloginattempts=:failedloginattempts,
                    suspended=:suspended
                 WHERE
                    iduser=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":id", $id);      
        $stmt->bindParam(":failedloginattempts", $failedloginattempts);     
        $stmt->bindValue(":suspended", $suspended ? 1 : 0);

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