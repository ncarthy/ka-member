<?php

namespace Controllers;

use DateTime;

/**
 * Controller to acomplish  tasks with multiple members
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-03-01
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class MembersCtl{

  public static function lifeAndHonorary(){  

    $model = new \Models\Members();

    echo json_encode($model->lifeAndHonorary(), JSON_NUMERIC_CHECK);
  }

  //$months is th enumber of months since last payment
  public static function lapsed($months){  

    $model = new \Models\Members();

    echo json_encode($model->lapsedMembers($months), JSON_NUMERIC_CHECK);
  }

  public static function cem(){  

    // set start and end of the period to examine
    // if no dates are provided then 
    //  - the end date defaults to a string representing today e.g. '2021-02-09'
    //  - the start date defaults to a string representing today minus 12 months 
    //    plus one day e.g. '2020-02-10'
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new \Models\Members();

    echo json_encode($model->contributingExMembers($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function discount(){  

    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new \Models\Members();

    echo json_encode($model->discountMembers($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function payingHonLife(){  
    
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new \Models\Members();

    echo json_encode($model->payingHonLifeMembers($start, $end), JSON_NUMERIC_CHECK);
  }


}

?>