<?php

namespace Controllers;

class CountryCtl{


  public static function read_all(){  

    $model = new \Models\Country();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }


  public static function read_one($id){  

    $model = new \Models\Country();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


  public static function read_one_name($name){  

    $model = new \Models\Country();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}