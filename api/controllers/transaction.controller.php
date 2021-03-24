<?php

namespace Controllers;

class TransactionCtl{

  public static function read_all(){  

    $model = new \Models\Transaction();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }

  public static function read_one($id){  

    $model = new \Models\Transaction();
    $model->id = $id;

    $tx = $model->readone();

    if (empty($model->amount) ) {
      http_response_code(422);   
      echo json_encode(
          array("message" => "No transaction found with id = " . $model->id)
      );
      exit(1);
    }

    echo json_encode($tx, JSON_NUMERIC_CHECK);
  }

  public static function read_by_idmember($idmember){  

    $model = new \Models\Transaction();
    $model->idmember = $idmember;

    echo json_encode($model->read_by_idmember(), JSON_NUMERIC_CHECK);
  }

  public static function create(){
    $model = new \Models\Transaction();

    $data = json_decode(file_get_contents("php://input"));
    TransactionCtl::transferParameters($data, $model);

    if($model->create()){
        echo json_encode(
            array(
                "message" => "New transaction with id=$model->id was created.",
                "id" => $model->id
            )
            , JSON_NUMERIC_CHECK);
    }
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to INSERT transaction.")
        );
    }
  }

  public static function update($idtransaction){
    $model = new \Models\Transaction();

    $model->id = $idtransaction;

    $data = json_decode(file_get_contents("php://input"));
    TransactionCtl::transferParameters($data, $model);

    if($model->update()){
        echo json_encode(
            array(
                "message" => "Transaction with id=$model->id was updated.",
                "id" => $model->id
            )
            , JSON_NUMERIC_CHECK);
    }
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to UPDATE transaction.")
        );
    }
  }

  public static function delete_by_idmember($idmember){  

    $model = new \Models\Transaction();
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

  public static function delete_by_id($idtransaction){  

    $model = new \Models\Transaction();
    $model->id = $idtransaction;

    if($model->delete_by_id()){
        echo json_encode(
            array("message" => "That transaction has been removed from the system.")
        );
    }
    
    // if unable to delete the member_name
    else{
        http_response_code(422); 
        echo json_encode(
            array("message" => "Unable to delete transaction.")
        );
    }
  }

  private static function transferParameters($data, $model)
  {
    $model->date = $data->date;
    $model->amount = $data->amount;
    $model->note = $data->note;
    $model->idmember = $data->idmember;
    $model->bankID = $data->bankID;
    $model->paymenttypeID = $data->paymenttypeID;
  }

}