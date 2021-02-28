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
    public function read(){

        //select all data
        $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`,
                    t.paymentmethod, t.member_idmember as idmember, t.`bankID`
                FROM
                    " . $this->table_name . " t
                ORDER BY t.idtransaction; ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        $items_arr=array();
        $items_arr["count"]=$num;
        $items_arr["records"]=array();

        if($num>0){       
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                extract($row);
            
                $item_item=array(
                    "idtransaction" => $id,
                    "date" => $date,
                    "amount" => $amount,
                    "paymentmethod" => html_entity_decode($paymentmethod),
                    "idmember" => $idmember,
                    "bankID" => $bankID
                );
    
                array_push($items_arr["records"], $item_item);
            }
        }

        return $items_arr;
    }

    // find the details of one user using $id
    public function readone(){

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

        $item = array(
            "id" => $this->id,
            "date" => $this->date,
            "paymentmethod" => html_entity_decode($this->paymentmethod),
            "amount" => $this->amount,
            "idmember" => $this->idmember,
            "bankID" => $this->bankID
        );

        return $item;
    }

        // find the details of transactions using $idmember
        public function read_by_idmember(){

            //select all data
            $query = "SELECT
                    t.idtransaction as `id`, t.`date`, t.`amount`,
                    t.paymentmethod, t.member_idmember as idmember, t.`bankID`
                    FROM
                    " . $this->table_name . " t
                    WHERE t.member_idmember = ?
                    ORDER BY t.`date` DESC";
                    
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $this->idmember);
            $stmt->execute();
            $num = $stmt->rowCount();

            $items_arr=array();
            $items_arr["count"]=$num;
            $items_arr["transactions"]=array();

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
                        "paymentmethod" => html_entity_decode($paymentmethod),
                        "amount" => $amount,
                        "idmember" => $idmember,
                        "bankID" => $bankID
                    );

                    array_push ($items_arr["transactions"], $item);
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
?>