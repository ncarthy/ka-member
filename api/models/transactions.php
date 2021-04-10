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
                    t.bankID,
                    COUNT(t.idtransaction) as `count`,
                    SUM(t.amount)  as `sum`
        FROM `transaction` t
        WHERE t.date >= :start AND t.date <= :end
        GROUP BY t.bankID,year(t.date),month(t.date)";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(":start", $this->startdate);
        $stmt->bindParam(":end", $this->enddate);

        $stmt->execute();
        $num = $stmt->rowCount();

        $items_arr=array();
        $items_arr["total"] = 0;
        $items_arr["count"] = 0;
        $items_arr["records"]=array();

        $sum_total =0;
        $count_total =0;

        if($num>0){       
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                extract($row);

                $item_item=array(
                    "index" => $index,
                    "bankID" => $bankID,
                    "count" => $count,
                    "sum" => $sum
                );

                $sum_total+=$sum;
                $count_total+=$count;

                array_push($items_arr["records"], $item_item);
            }

            $items_arr["total"] = $sum_total;
            $items_arr["count"] = $count_total;
        }

        return $items_arr;
    }
}