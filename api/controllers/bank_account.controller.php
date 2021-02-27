<?php

namespace Controllers;


class BankAccountCtl{

  public static function read_all(){  

    $model = new \Models\BankAccount();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }


  public static function read_one($id){  

    $model = new \Models\BankAccount();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


  public static function read_one_name($name){  

    $model = new \Models\BankAccount();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}