<?php

namespace Models;

use \PDO;

class User{
    // database conn 
    private $conn;
    // table name
    private $table_name = "user";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $id;
    public $username;
    public $role;
    public $suspended;
    public $password;
    public $fullname;
    public $failedloginattempts;

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    u.iduser as `id`, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.failedloginattempts
                    FROM
                    " . $this->table_name . " u " . 
                    (isset($this->suspended)?'WHERE suspended = '.$this->suspended.' ':'');                    

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        $num = $stmt->rowCount();

        $users_arr=array();

        if($num>0){
 
            
        
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                    $user_item=array(
                        "id" => $id,
                        "username" => $username,
                        "fullname" => html_entity_decode($name),
                        "role" => $isAdmin?'Admin':'User',
                        "suspended" => $suspended?true:false
                    );
        
                    // create nonindexed array
                    array_push ($users_arr, $user_item);
                }
               
        }

        return $users_arr;
    }

    // find the details of one user using $id
    public function readOne(){

        //select all data
        $query = "SELECT
                    u.`iduser` as id, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.`failedloginattempts`
                    FROM
                    " . $this->table_name . " u
                    WHERE u.iduser = :id
                    LIMIT 0,1";
                
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam (":id", $id, PDO::PARAM_INT);

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
            $this->role = $row['isAdmin'] ? 'Admin' : 'User';
            $this->suspended = $row['suspended']?true:false;
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

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    name=:fullname,
                    suspended=:suspended,
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',new_pass=:password ':'');
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->role=htmlspecialchars(strip_tags($this->role));
        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        $isadmin = ($this->role=='Admin') ? 1 : 0;
        $suspended = $this->suspended ? 1 : 0;

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $isadmin, PDO::PARAM_INT);
        $stmt->bindParam(":suspended", $suspended, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":failedloginattempts", $this->failedloginattempts, PDO::PARAM_INT);
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
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->role=htmlspecialchars(strip_tags($this->role));
        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }
        $isadmin = ($this->role=='Admin') ? 1 : 0;
        $suspended = $this->suspended ? 1 : 0;

        $this->failedloginattempts = !empty($this->failedloginattempts) ? $this->failedloginattempts : 0;

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $isadmin, PDO::PARAM_INT);
        $stmt->bindParam(":suspended", $suspended, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $this->fullname);        
        $stmt->bindParam(":failedloginattempts", $this->failedloginattempts, PDO::PARAM_INT);     

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE iduser = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);

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

    
    public function checkPassword($pwd, &$errors) {
        $errors_init = $errors;
    
        if (strlen($pwd) < 8) {
            $errors[] = "Password too short!";
        }
    
        if (!preg_match("#[0-9]+#", $pwd)) {
            $errors[] = "Password must include at least one number!";
        }
    
        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $errors[] = "Password must include at least one letter!";
        }     
    
        return ($errors == $errors_init);
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