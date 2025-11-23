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
    public $email;
    public $suspended;
    public $password;
    public $fullname;
    public $title;
    public $failedloginattempts;

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    u.iduser as `id`, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.failedloginattempts, 
                    u.`email`, u.`title`
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
                        "fullname" => html_entity_decode($name??''),
                        "role" => $isAdmin?'Admin':'User',
                        "suspended" => $suspended?true:false,
                        "email" => html_entity_decode($email??''),
                        "title" => html_entity_decode($title??'')
                    );
        
                    // create nonindexed array
                    array_push ($users_arr, $user_item);
                }
               
        }

        return $users_arr;
    }

    /**
     * Query for the details of one user using $username but return the results as a 
     * MySQLi statement rather than a JSON string or an object
     * 
     * @return object Returns a MySQLi statement
     */
    public function readOneByUsername(){
        return User::prepareAndExecuteSelectStatement('BY_USERNAME');
    }

    /**
     * Retrieve from the database details of a User, queried using the 
     * model property $id
     * 
     * @return void
     * 
     */
    public function readOneByUserID(){

        // execute query
        $stmt = User::prepareAndExecuteSelectStatement('BY_USERID');

        $this->transferPropertiestoModel($stmt);
    }

    // find the details of one user using $username
    public function readOneRaw($username){

        //select all data
        $query = "SELECT
                    u.`iduser` as id, u.`username`, u.`new_pass` as `password`,
                    u.isAdmin, u.suspended, u.`name`, u.`failedloginattempts`,
                    CASE WHEN u.isAdmin THEN 'Admin' ELSE 'User' END as `role`,
                    u.`email`, u.`title`
                    FROM
                    " . $this->table_name . " u
                    WHERE username = ?
                    LIMIT 0,1";
                
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $username);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Add a new User to the database.
     * 
     * @return bool 'true' if database insert succeeded.
     * 
     */
    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    name=:fullname,
                    email=:email,
                    title=:title,
                    suspended=:suspended,
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',new_pass=:password ':'');
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->role=htmlspecialchars(strip_tags($this->role));
        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        $isadmin = ($this->role=='Admin') ? 1 : 0;
        $suspended = $this->suspended ? 1 : 0;

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $isadmin, PDO::PARAM_INT);
        $stmt->bindParam(":suspended", $suspended, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":title", $this->title);
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

     /**
     * Update an existing User in the database with new data.
     * 
     * @return bool 'true' if database update succeeded.
     * 
     */   
    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    suspended=:suspended,
                    email=:email,
                    title=:title,
                    name=:fullname,
                    timestamp=NULL,
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',new_pass=:password ':'') ."
                 WHERE
                    iduser=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->title=htmlspecialchars(strip_tags($this->title));
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
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":title", $this->title);
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

    /**
     * Delete the user from the database that matches the id property 
     * of the user.
     * 
     * @return bool 'true' if database delete succeeded.
     * 
     */
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

    /**
     * Update 2 fields of the user: failedloginattempts and suspended.
     *
     * @param int $id The id of the user to update.
     * @param int $failedloginattempts The number of failed attempts to login.
     * @param bool $suspendUser If 'true' then the user will be set to 'suspended'. 
     * If 'false' then 'suspended' is unset.
     * 
     * @return bool 'true' if database update succeeded.
     * 
     */
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

    /**
     * Check the supplied password meets minimum standards:
     *  - 8 or more characters
     *  - Must include at least one number
     *  - Must include ast least one letter
     *
     * @param string $pwd The password to test
     * @param array $errors An array of errors. Empty if no errors.
     * 
     * @return bool 'true' if password passess the tests
     * 
     */
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

 /**
     * Build and execute a MySQLi statement to query the database for a user or users. The
     * query is customised by the where query specifier. This method was written to reduce
     * code re-use in the various read... methods.
     * 
     * @param string $whereQuery One of '','BY_USERID', 'BY_USERNAME','BY_SUSPENDED'
     * 
     * @return object Returns a MySQLi statement
     */
    private function prepareAndExecuteSelectStatement(string $whereQuery) {
        
        $query = "SELECT
                    u.`iduser`, u.`username`, u.`new_pass`, u.`name`,
                    u.isAdmin, u.suspended, u.`failedloginattempts`,
                    CASE WHEN u.isAdmin THEN 'Admin' ELSE 'User' END as `role`,
                    u.`email`, u.`title`
                    FROM
                    " . $this->table_name . " u";
                
        switch ($whereQuery) {
            case 'BY_USERID':
                $query .= " WHERE u.iduser = :id";
                break;
            case 'BY_USERNAME':
                $query .= " WHERE u.username = :username";
                break;             
            case 'BY_SUSPENDED':
                $query .= 
                    (isset($this->suspended)?' WHERE suspended = '.$this->suspended.' ':'');   
                break;            
        }             

        if (!$this->conn) {
            return;
        }
        
        $stmt = $this->conn->prepare( $query );

        switch ($whereQuery) {
            case 'BY_USERID':
                $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
                $stmt->bindParam (":id", $id, PDO::PARAM_INT);
                break;
            case 'BY_USERNAME':
                $this->username=htmlspecialchars(strip_tags($this->username));
                $stmt->bindParam(":username", $this->username);
                break;                          
        }   

        $stmt->execute();

        return $stmt;
    }

    /**
     * Update the properties of the user model with the data from the database
     * 
     * @return void
     */
    private function transferPropertiestoModel($stmt) {

        if (!$stmt) return;

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // set values to object properties
        if ( !empty($row) ) {
            $this->id = $row['iduser'];
            $this->username = $row['username'];
            $this->fullname = $row['name'];
            $this->password = $row['new_pass'];
            $this->email = $row['email'];
            $this->title = $row['title'];
            $this->role = $row['isAdmin'] ? 'Admin' : 'User';
            $this->suspended = $row['suspended']?true:false;
            $this->failedloginattempts = $row['failedloginattempts'];
        }
    }
}