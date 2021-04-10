<?php

namespace Models;

use \PDO;

class Transactions{

    private $conn;
    public $startdate;
    public $enddate;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    function summary_by_month(){
        
        $query = "SELECT CONCAT(year(t.date),'-',month(t.date)) as `index`,
                    t.bankID, ba.name as bankaccount, 
                    COUNT(t.idtransaction) as `count`,
                    SUM(t.amount)  as `sum`
        FROM `transaction` t
        JOIN bankaccount ba ON t.bankID = ba.bankID
        WHERE t.date >= :start AND t.date <= :end
        GROUP BY t.bankID,year(t.date),month(t.date)";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(":start", $this->startdate);
        $stmt->bindParam(":end", $this->enddate);

        $stmt->execute();
        $num = $stmt->rowCount();

        $items_arr=array();

        if($num>0){       
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                extract($row);

                $item_item=array(
                    "index" => $index,
                    "bankID" => $bankID,
                    "bankname" => $bankaccount,
                    "count" => $count,
                    "sum" => $sum
                );

                array_push($items_arr, $item_item);
            }
        }

        return $items_arr;
    }
}