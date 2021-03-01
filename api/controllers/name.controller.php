<?php

namespace Controllers;

class MemberNameCtl{

  public static function read_by_id($idmembername){  

    $model = new \Models\MemberName();
    $model->id = $idmembername;

    echo json_encode($model->read_by_id(), JSON_NUMERIC_CHECK);
  }

  public static function read_by_idmember($idmember){  

    $model = new \Models\MemberName();
    $model->idmember = $idmember;

    echo json_encode($model->read_by_idmember(), JSON_NUMERIC_CHECK);
  }

  public static function delete_by_idmember($idmember){  

    $model = new \Models\MemberName();
    $model->idmember = $idmember;

    if($model->delete_by_idmember()){
        echo json_encode(
            array("message" => "All names for that member were removed from the system.")
        );
    }
    
    // if unable to delete the member_name
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to delete member name.")
        );
    }
  }

  public static function delete_by_id($idmembername){  

    $model = new \Models\MemberName();
    $model->id = $idmembername;

    if($model->delete_by_id()){
        echo json_encode(
            array("message" => "That name has been removed from the system.")
        );
    }
    
    // if unable to delete the member_name
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to delete member name.")
        );
    }
  }


  public static function create(){
    $model = new \Models\MemberName();

    $data = json_decode(file_get_contents("php://input"));

    $model->honorific = $data->honorific;
    $model->firstname = $data->firstname;
    $model->surname = $data->surname;
    $model->idmember = $data->idmember;

    if($model->create()){
        echo json_encode(
            array(
                "message" => "New name with idmembername=$model->id was created.",
                "idmembername" => $model->id
            )
            , JSON_NUMERIC_CHECK);
    }
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to INSERT member name.")
        );
    }
  }

  public static function update($idmembername){
    $model = new \Models\MemberName();

    $data = json_decode(file_get_contents("php://input"));

    $model->id = $idmembername;
    $model->honorific = $data->honorific;
    $model->firstname = $data->firstname;
    $model->surname = $data->surname;
    // cannot update idmember

    if($model->update()){
        echo json_encode(
            array(
                "message" => "New name with idmembername=$model->id was updated.",
                "idmembername" => $model->id
            )
            , JSON_NUMERIC_CHECK);
    }
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to UPDATE member name.")
        );
    }
  }
}