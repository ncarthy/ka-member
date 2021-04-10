<?php

namespace Controllers;

use DateTime;
use DateInterval;

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
      
    public static function detail_by_month(){  

        $model = new \Models\Transactions();

        if(isset($_GET['month']) && isset($_GET['year'])) {
            $start = new DateTime($_GET['year'].'-'.$_GET['month'].'-01');
            $model->startdate = $start->format('Y-m-d');
            $end = $start->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));
            $model->enddate = $end->format('Y-m-d');
        } else {
            http_response_code(422);  
            echo json_encode(
              array(
                "message" => "Unable to retrieve transactions. Please set MONTH & YEAR."
              )
              , JSON_NUMERIC_CHECK);
        }

        if (isset($_GET['bankID']) && !empty($_GET['bankID'])) {
            $model->bankID = $_GET['bankID'];
      }
    
        echo json_encode($model->detail_by_month(), JSON_NUMERIC_CHECK);
      }

}