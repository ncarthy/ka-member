<?php
class Database{
    // specify your own database credentials
    private $host = "192.168.1.22";
    private $db_name = "knightsb_membership";
    private $username = "knightsb_member";
    private $password = "SsP4qIm4omu4M";
    public $conn;

    // get the database connection
    public function getConnection(){

        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name
                , $this->username, $this->password);
            $this->conn->exec("set names utf8");    

            // From https://stackoverflow.com/a/60496/6941165
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
                
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>