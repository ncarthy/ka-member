<?php

namespace Controllers;

/**
 * Controller to acomplish Member related tasks
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-02-28
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class MemberCtl{

  public static function read_all(){  

    $model = new \Models\Member();

    // Substitution trick to preserve phone numbers as strings
    // Phone numbers are returned with the string 'xn#' prefixed
    $encoded_json = json_encode($model->read(), JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
    echo str_replace('xn#','',$encoded_json);
  }

  public static function read_one($id){  

    $model = new \Models\Member();
    $model->id = $id;

    $model->readOne();

    if (empty($model->username) ) {
      http_response_code(422);   
      echo json_encode(
          array("message" => "No member found with id = " . $model->id)
      );
      exit(1);
    }

    $member=array(
        "id" => $model->id,
        "title" => $model->title,
        "businessname" => $model->businessname,
        "bankpayerref" => $model->bankpayerref,
        "note" => $model->note,
        "addressfirstline" => $model->addressfirstline,
        "addresssecondline" => $model->addresssecondline,
        "city" => $model->city,
        "county" => $model->county,
        "postcode" => $model->postcode,
        "countryID" => $model->countryID,
        "area" => $model->area,
        "email1" => $model->email1,
        "phone1" => 'xn#'.$model->phone1,// Substitution trick to preserve phone numbers as strings
        "addressfirstline2" => $model->addressfirstline2,
        "addresssecondline2" => $model->addresssecondline2,
        "city2" => $model->city2,
        "county2" => $model->county2,
        "postcode2" => $model->postcode2,
        "country2ID" => $model->country2ID,
        "email2" => $model->email2,
        "phone2" => 'xn#'.$model->phone2, // Substitution trick to preserve phone numbers as strings
        "statusID" => $model->statusID,
        "expirydate" => $model->expirydate,
        "joindate" => $model->joindate,
        "reminderdate" => $model->reminderdate,
        "updatedate" => $model->updatedate,
        "deletedate" => $model->deletedate,
        "repeatpayment" => $model->repeatpayment,
        "recurringpayment" => $model->recurringpayment,
        "username" => $model->username,
        "gdpr_email" => $model->gdpr_email,
        "gdpr_tel" => $model->gdpr_tel,
        "gdpr_address" => $model->gdpr_address,
        "gdpr_sm" => $model->gdpr_sm,    
        "postonhold" => $model->postonhold
    );

    // Substitution trick to preserve phone numbers as strings
    $encoded_json = json_encode($member, JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
    echo str_replace('xn#','',$encoded_json);
  }

  public static function create(){

    $model = new \Models\Member();
    $data = json_decode(file_get_contents("php://input"));
    MemberCtl::transferParameters($data, $model);
    
    // INSERT the row into the database
    if( $model->create()) {
      echo json_encode(
        array(
          "message" => "New member with id=$model->id was created.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
      // if unable to create the model, tell the admin
        http_response_code(422);  
        echo json_encode(
          array("message" => "Unable to INSERT row.")
        );
    }
  }

  public static function update($id){

    $model = new \Models\Member();
    $data = json_decode(file_get_contents("php://input"));
    $model->id = $id;
    MemberCtl::transferParameters($data, $model);

    if (isset($data->password)) {
      $model->password = password_hash($data->password, PASSWORD_DEFAULT);
      $model->checkPassword($data->password, $errors);
      if ($errors) {
          http_response_code(422);  
          echo json_encode(
            array("message" => implode(" & ",$errors))
          );
      } 
      exit(1);
    }

    if( $model->update()) {
      echo json_encode(
        array(
          "message" => "User with id=$model->id was updated.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(422);  
        echo json_encode(
          array(
            "message" => "Unable to UPDATE row.",
            "id" => $model->id
          )
          , JSON_NUMERIC_CHECK);
    }
  }


  public static function delete($id){
    $model = new \Models\Member();

    $model->id = $id;

    if( $model->delete()) {
      echo json_encode(
        array(
          "message" => "Member with id=$model->id was deleted.",
          "id" => $model->id
          , JSON_NUMERIC_CHECK)
      , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(422);  
        echo json_encode(
          array(
            "message" => "Unable to DELETE row.",
            "id" => $model->id
            , JSON_NUMERIC_CHECK)
        );
    }
  }

  public static function patch($id){
    $model = new \Models\Member();
    $model->id = $id;
    $model->username = MemberCtl::username();

    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->method)){

        switch (strtolower($data->method)) {
            case 'settoformer':
                $model->setToFormerMember();
                break;
            case 'anonymize':
                $model->anonymize();
                break;
            default:
            http_response_code(422);  
            echo json_encode(
              array(
                "message" => "Unknown method",
                "method" => $data->method
              )
            );
        }
    }

  }

  private static function transferParameters($data, $model)
  {
    $model->title = $data->title;
    $model->businessname = $data->businessname;
    $model->bankpayerref = $data->bankpayerref;
    $model->note = $data->note;
    $model->addressfirstline = $data->addressfirstline;
    $model->addresssecondline = $data->addresssecondline;
    $model->city = $data->city;
    $model->county = $data->county;
    $model->postcode = $data->postcode;
    $model->countryID = $data->countryID;
    $model->area = $data->area;
    $model->email1 = $data->email1;
    $model->phone1 = $data->phone1;
    $model->addressfirstline2 = $data->addressfirstline2;
    $model->addresssecondline2 = $data->addresssecondline2;
    $model->city2 = $data->city2;
    $model->county2 = $data->county2;
    $model->postcode2 = $data->postcode2;
    $model->country2ID = $data->country2ID;
    $model->email2 = $data->email2;
    $model->phone2 = $data->phone2;
    $model->statusID = $data->statusID;
    $model->expirydate = $data->expirydate;
    $model->joindate = $data->joindate;
    $model->updatedate = null; // will insert current_timestamp
    $model->reminderdate = $data->reminderdate;
    $model->deletedate = $data->deletedate;
    $model->repeatpayment = $data->repeatpayment;
    $model->recurringpayment = $data->recurringpayment;
    $model->username = MemberCtl::username();
    $model->gdpr_email = $data->gdpr_email;
    $model->gdpr_tel = $data->gdpr_tel;
    $model->gdpr_address = $data->gdpr_address;
    $model->gdpr_sm = $data->gdpr_sm;
    $model->postonhold = $data->postonhold;
  }

  private static function username(){
    $jwt = new \Models\JWTWrapper();
    return $jwt->user;
  }
}