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
        "area" => $model->area,
        "email1" => $model->email1,
        "phone1" => 'xn#'.$model->phone1,// Substitution trick to preserve phone numbers as strings
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
        "postonhold" => $model->postonhold,
        "primaryAddress" => array(
          "addressfirstline" => $model->addressfirstline,
          "addresssecondline" => $model->addresssecondline,
          "city" => $model->city,
          "county" => $model->county,
          "postcode" => $model->postcode,
          "country" => $model->countryID
        ),
        "secondaryAddress" => array(
          "addressfirstline" => $model->addressfirstline2,
          "addresssecondline" => $model->addresssecondline2,
          "city" => $model->city2,
          "county" => $model->county2,
          "postcode" => $model->postcode2,
          "country" => $model->country2ID
        ),
        "multiplier" => $model->multiplier,
        "membershipfee" => $model->membershipfee
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
          "id" => $model->id)
          , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(422);  
        echo json_encode(
          array(
            "message" => "Unable to DELETE row.",
            "id" => $model->id)
            , JSON_NUMERIC_CHECK);
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
    // Flatten addresses
    if (empty($data->primaryAddress)) {
      $model->addressfirstline = empty($data->addressfirstline)?null:$data->addressfirstline;
      $model->addresssecondline = empty($data->addresssecondline)?null:$data->addresssecondline;
      $model->city = empty($data->city)?null:$data->city;
      $model->county = empty($data->county)?null:$data->county;
      $model->postcode = empty($data->postcode)?null:$data->postcode;
      $model->countryID = empty($data->countryID)?null:$data->countryID;
    } else {
      $model->addressfirstline = $data->primaryAddress->addressfirstline;
      $model->addresssecondline = $data->primaryAddress->addresssecondline;
      $model->city = $data->primaryAddress->city;
      $model->county = $data->primaryAddress->county;
      $model->postcode = $data->primaryAddress->postcode;
      $model->countryID = $data->primaryAddress->country;
    }
    if (empty($data->secondaryAddress)) {
      $model->addressfirstline2 = empty($data->addressfirstline)?null:$data->addressfirstline;
      $model->addresssecondline2 = empty($data->addresssecondline)?null:$data->addresssecondline;
      $model->city2 = empty($data->city)?null:$data->city;
      $model->county2 = empty($data->county)?null:$data->county;
      $model->postcode2 = empty($data->postcode)?null:$data->postcode;
      $model->country2ID = empty($data->countryID)?null:$data->countryID;
    } else {
      $model->addressfirstline2 = $data->secondaryAddress->addressfirstline;
      $model->addresssecondline2 = $data->secondaryAddress->addresssecondline;
      $model->city2 = $data->secondaryAddress->city;
      $model->county2 = $data->secondaryAddress->county;
      $model->postcode2 = $data->secondaryAddress->postcode;
      $model->country2ID = $data->secondaryAddress->country;
    }

    // flatten emails & telephone numbers
    if (empty($data->emails)) {
      $model->email1 = $data->email1;
      $model->email2 = $data->email2;
    } else {
      $model->email1 = empty($data->emails->email1)?null:$data->emails->email1;
      $model->email2 = empty($data->emails->email2)?null:$data->emails->email2;
    }
    if (empty($data->phones)) {
      $model->phone1 = $data->phone1;
      $model->phone2 = $data->phone2;
    } else {
      $model->phone1 = empty($data->phones->phone1)?null:$data->phones->phone1;
      $model->phone2 = empty($data->phones->phone2)?null:$data->phones->emaiphone2l2;
    }

    // Do other parameters
    $model->title = $data->title;
    $model->businessname = $data->businessname;
    $model->bankpayerref = $data->bankpayerref;
    $model->note = $data->note;
    $model->gdpr_email = $data->gdpr_email;
    $model->gdpr_tel = $data->gdpr_tel;
    $model->gdpr_address = $data->gdpr_address;
    $model->gdpr_sm = $data->gdpr_sm;
    $model->postonhold = $data->postonhold;
    $model->statusID = $data->statusID;
    $model->expirydate = $data->expirydate;
    $model->joindate = $data->joindate;
    $model->reminderdate = $data->reminderdate;

    // Audit parameters
    $model->updatedate = null; // will insert current_timestamp
    $model->username = MemberCtl::username();   

    // Optional parameters
    $model->area = empty($data->area)?'':$data->area;
    $model->deletedate = empty($data->deletedate)?null:$data->deletedate;
    $model->repeatpayment = empty($data->repeatpayment)?0:$data->repeatpayment;
    $model->recurringpayment =  empty($data->recurringpayment)?0:$data->recurringpayment;
    $model->multiplier = empty($data->multiplier)?null:$data->multiplier;
    $model->membershipfee = empty($data->membershipfee)?null:$data->membershipfee;
  }

  private static function username(){
    $jwt = new \Models\JWTWrapper();
    return $jwt->user;
  }
}