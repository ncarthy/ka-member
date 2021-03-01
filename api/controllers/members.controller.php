<?php

namespace Controllers;

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


}

?>