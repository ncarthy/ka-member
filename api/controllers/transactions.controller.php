<?php

namespace Controllers;

class TransactionsCtl{

    public static function summary_by_month(){  

        $model = new \Models\Transactions();

        if(isset($_GET['start']) || isset($_GET['end'])) {
            $start='';
            $end='';
            list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                        !isset($_GET['start']) ? '' : $_GET['start'], 
                                        !isset($_GET['end']) ? '' : $_GET['end']
                                    );
        
            $model->startdate = $start;
            $model->enddate = $end;
        } else {
            $model->startdate = '2000-01-01';
            $model->enddate = date('Y-m-d');
        }

        if (isset($_GET['bankID']) && !empty($_GET['bankID'])) {
            $model->bankID = $_GET['bankID'];
      }
    
        echo json_encode($model->summary_by_month(), JSON_NUMERIC_CHECK);
      }

}