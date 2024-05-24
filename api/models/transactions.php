<?php

namespace Models;

use \PDO;

class Transactions{

    private $conn;
    public $startdate;
    public $enddate;
    public $bankID;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    function summary_by_month(){
        
        $query = "SELECT CONCAT(year(t.date),'-',month(t.date)) as `index`,
                    t.bankID,
                    COUNT(t.idtransaction) as `count`,
                    SUM(t.amount)  as `sum`
        FROM `transaction` t
        WHERE t.date >= :start AND t.date <= :end" . 

        ($this->bankID ? ' AND bankID = :bankID ' : ' ') .

        "GROUP BY t.bankID,year(t.date),month(t.date);";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(":start", $this->startdate);
        $stmt->bindParam(":end", $this->enddate);
        if ($this->bankID) {
            $bankID = filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":bankID", $bankID, PDO::PARAM_INT);
        }

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

    function detail_by_month(){
        
        $query = "SELECT t.`idtransaction`,t.`date`,t.amount,t.paymenttypeID,
                    t.member_idmember,t.bankID,t.note,
                    IFNULL(GROUP_CONCAT(CONCAT(CASE
                                            WHEN `mn`.`honorific` = '' THEN ''
                                            ELSE CONCAT(`mn`.`honorific`, ' ')
                                        END,
                                        CASE
                                            WHEN `mn`.`firstname` = '' THEN ''
                                            ELSE CONCAT(`mn`.`firstname`, ' ')
                                        END,
                                        `mn`.`surname`)
                                SEPARATOR ' & '),
                            '') AS `name`,
                    CONCAT(`m`.`businessname`, '') AS `businessname`
                    FROM `transaction` t
                    INNER JOIN `member` m ON t.member_idmember = m.idmember
                    INNER JOIN `membername` mn ON m.idmember = mn.member_idmember
                    WHERE t.`date` >= :start AND t.`date` <= :end" . 
                        ($this->bankID ? ' AND t.`bankID` = :bankID ' : ' ') .
                    "GROUP BY t.`idtransaction`, t.`member_idmember` 
                    ORDER BY t.`date`;";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(":start", $this->startdate);
        $stmt->bindParam(":end", $this->enddate);
        if ($this->bankID) {
            $bankID = filter_var($this->bankID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":bankID", $bankID, PDO::PARAM_INT);
        }

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
                    "idtransaction" => $idtransaction,
                    "date" => $date,
                    "bankID" => $bankID,
                    "amount" => $amount,
                    "paymenttypeID" => $paymenttypeID,
                    "idmember" => $member_idmember,
                    "note" => $note ?? '',
                    "name" => $name,
                    "businessname" => $businessname
                );

                $sum_total+=$amount;
                $count_total++;

                array_push($items_arr["records"], $item_item);
            }

            $items_arr["total"] = $sum_total;
            $items_arr["count"] = $count_total;
        }

        return $items_arr;
    }
}