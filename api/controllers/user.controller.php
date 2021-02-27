<?php

namespace Controllers;

/**
 * Controller to acomplish USer related tasks
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-02-27
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class UserCtl{

  public static function read_all(){  

    $model = new \Models\User();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }

  public static function read_one($id){  

    $model = new \Models\User();
    $model->id = $id;

    $model->readOne();

    $user = array(
        "id" => $model->id,
        "username" => $model->username,
        "fullname" => html_entity_decode($model->fullname),
        "role" => $model->role,
        "isadmin" => $model->role=='Admin' ? true : false,
        "suspended" => $model->suspended
    );

    echo json_encode($user, JSON_NUMERIC_CHECK);
  }

}