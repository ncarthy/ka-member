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
    public $paymenttypeID;
    public $idmember;
    public $bankID;
    public $note;
    
    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    date=:date,
                    amount=:amount, 
                    paymenttypeID=:paymenttypeID,
                    member_idmember=:idmember,
                    bankID=:bankID,
                    note=:note
                    ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->amount=filter_var($this->amount, FILTER_SANITIZE_NUMBER_FLOAT,
                                                            FILTER_FLAG_ALLOW_FRACTION);        
        $this->note=htmlspecialchars(strip_tags($this->note));
        $this->idmember=filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->bankID=filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);
        $this->paymenttypeID=filter_var($this->paymenttypeID, FILTER_SANITIZE_NUMBER_INT);

        // bind values
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":note", $this->note);
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        $stmt->bindParam(":bankID", $this->bankID, PDO::PARAM_INT);
        $stmt->bindParam(":paymenttypeID", $this->paymenttypeID, PDO::PARAM_INT);
        
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
                    note=:note,
                    member_idmember=:idmember,
                    bankID=:bankID,
                    paymenttypeID=:paymenttypeID
                 WHERE
                    idtransaction=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->amount=filter_var($this->amount, FILTER_SANITIZE_NUMBER_FLOAT,
                                                            FILTER_FLAG_ALLOW_FRACTION);        
        $this->note=htmlspecialchars(strip_tags($this->note));
        $this->idmember=filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->bankID=filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);
        $this->paymenttypeID=filter_var($this->paymenttypeID, FILTER_SANITIZE_NUMBER_INT);
        
        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":note", $this->note);
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        $stmt->bindParam(":bankID", $this->bankID, PDO::PARAM_INT);
        $stmt->bindParam(":paymenttypeID", $this->paymenttypeID, PDO::PARAM_INT);      

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`, t.`note`,
                    t.paymenttypeID, t.member_idmember as idmember, t.`bankID`
                FROM
                    " . $this->table_name . " t
                ORDER BY t.idtransaction; ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        $items_arr=array();

        if($num>0){       
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                extract($row);
            
                $item_item=array(
                    "idtransaction" => $id,
                    "date" => $date,
                    "amount" => $amount,
                    "paymenttypeID" => $paymenttypeID,
                    "idmember" => $idmember,
                    "bankID" => $bankID,
                    "note" => html_entity_decode($note)
                );
    
                array_push($items_arr, $item_item);
            }
        }

        return $items_arr;
    }

    /**
     * Return a single transaction
     */
    public function readone(){

        //select all data
        $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`, t.`note`,
                    t.paymenttypeID, t.member_idmember as idmember, t.`bankID`
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
            $this->note = $row['note'];
            $this->idmember = $row['idmember'];
            $this->bankID = $row['bankID'];
            $this->paymenttypeID = $row['paymenttypeID'];
        }

        $item = array(
            "id" => $this->id,
            "date" => $this->date,
            "note" => html_entity_decode($this->note),
            "amount" => $this->amount,
            "idmember" => $this->idmember,
            "bankID" => $this->bankID,
            "paymenttypeID" => $this->paymenttypeID
        );

        return $item;
    }

        // find the details of transactions using $idmember
        public function read_by_idmember(){

            //select all data
            $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`, t.`note`,
                    t.paymenttypeID, t.member_idmember as idmember, t.`bankID`
                    FROM
                    " . $this->table_name . " t
                    WHERE t.member_idmember = ?
                    ORDER BY t.`date` DESC";
                    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $this->idmember);
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
                
                    $item = array(
                        "id" => $id,
                        "date" => $date,
                        "note" => html_entity_decode($note ?? ''),
                        "amount" => $amount,
                        "idmember" => $idmember,
                        "bankID" => $bankID,
                        "paymenttypeID" => $paymenttypeID
                    );

                    array_push ($items_arr, $item);
                }
            }

            return $items_arr;

        }

    function delete_by_id(){
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

        /* Delete all transactions for a member from the database by providing the idmember FK */
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
}