<?php

namespace Controllers;

class MemberNameCtl{

  public static function read_by_id($id){  

    $model = new \Models\MemberName();
    $model->id = $id;

    echo json_encode($model->read_by_id(), JSON_NUMERIC_CHECK);
  }

  public static function read_by_idmember($idmember){  

    $model = new \Models\MemberName();
    $model->idmember = $idmember;

    echo json_encode($model->read_by_idmember(), JSON_NUMERIC_CHECK);
  }

}