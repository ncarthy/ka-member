<?php

namespace Controllers;


class PaymentTypeCtl{

  public static function read_all(){  

    $model = new \Models\PaymentType();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }


  public static function read_one($id){  

    $model = new \Models\PaymentType();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


  public static function read_one_name($name){  

    $model = new \Models\PaymentType();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}