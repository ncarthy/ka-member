<?php

namespace Controllers;

/**
 * Controller to summarise membership numbers
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-03-01
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class MembersSummaryCtl{

  public static function activeMembersByType(){  

    $model = new \Models\MembersSummary();

    echo json_encode($model->activeMembersByType(), JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
  }

}
