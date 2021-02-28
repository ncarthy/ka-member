<?php

namespace Models;

use \PDO;

class Transaction{
    // database conn 
    private $conn;
    // table name
    private $table_name = "transaction";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $id;
    public $date;
    public $amount;
    public $paymentmethod;
    public $idmember;
    public $bankID;
    
    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    date=:date,
                    amount=:amount, 
                    paymentmethod=:paymentmethod,
                    member_idmember=:idmember,
                    bankID=:bankID
                    ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->amount=filter_var($this->amount, FILTER_SANITIZE_NUMBER_FLOAT,
                                                            FILTER_FLAG_ALLOW_FRACTION);        
        $this->paymentmethod=htmlspecialchars(strip_tags($this->paymentmethod));
        $this->idmember=filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->bankID=filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);

        $this->bankID = !empty($this->bankID) ? $this->bankID : NULL;

        // bind values
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":paymentmethod", $this->paymentmethod);
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        $stmt->bindParam(":bankID", $this->bankID, PDO::PARAM_INT);
        
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
                    date=:date,
                    amount=:amount, 
                    paymentmethod=:paymentmethod,
                    member_idmember=:idmember,
                    bankID=:bankID
                 WHERE
                    idtransaction=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->amount=filter_var($this->amount, FILTER_SANITIZE_NUMBER_FLOAT,
                                                            FILTER_FLAG_ALLOW_FRACTION);        
        $this->paymentmethod=htmlspecialchars(strip_tags($this->paymentmethod));
        $this->idmember=filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->bankID=filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);

        $this->bankID = !empty($this->bankID) ? $this->bankID : NULL;
        
        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":paymentmethod", $this->paymentmethod);
        $stmt->bindParam(":idmember", $this->idmember);
        $stmt->bindParam(":bankID", $this->bankID);       

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    // used by select drop-down list
    public function readAll(){

        //select all data
        $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`,
                    t.paymentmethod, t.member_idmember as idmember, t.`bankID`
                FROM
                    " . $this->table_name . " t
                ORDER BY t.idtransaction; ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    // find the details of one user using $id
    public function readOne(){

        //select all data
        $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`,
                    t.paymentmethod, t.member_idmember as idmember, t.`bankID`
                    FROM
                    " . $this->table_name . " t
                    WHERE t.idtransaction = ?
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
            $this->date = $row['date'];
            $this->amount = $row['amount'];
            $this->paymentmethod = $row['paymentmethod'];
            $this->idmember = $row['idmember'];
            $this->bankID = $row['bankID'];
        }
    }

        // find the details of transactions using $idmember
        public function readMember($idmember){

            //select all data
            $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`,
                    t.paymentmethod, t.member_idmember as idmember, t.`bankID`
                    FROM
                    " . $this->table_name . " t
                    WHERE t.member_idmember = ?
                    ORDER BY t.`date` DESC";
                    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $idmember);
            $stmt->execute();
    
            return $stmt;
        }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE idtransaction = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // used for paging
    public function count(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
    
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_rows'];
    }
}
?>