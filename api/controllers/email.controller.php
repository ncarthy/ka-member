<?php

namespace Controllers;

use \Models\MembershipStatus;

class EmailCtl{


  public static function send_switchrequest(){

    $model = EmailCtl::getEmailModel();
    
    if( $model->prepare_switchrequest()) {
      if ($model->send()) {
        echo json_encode(
          array("message" => "Success")
        );
      } else {
        http_response_code(422);  
        echo json_encode(
          array("message" => "Unable to send email.")
        );          
      }
    } else{
          http_response_code(422);  
          echo json_encode(
            array("message" => "Unable to prepare email.")
          );
      }
    }

    public static function prepare_switchrequest(){

      $model = EmailCtl::getEmailModel();
      
      if( $model->prepare_switchrequest()) {
        echo json_encode(
          array(
            "html" => $model->body
          )
        );
      } else{
          http_response_code(422);  
          echo json_encode(
            array("message" => "Unable to prepare email.")
          );
      }
    }

    public static function send_reminder(){

      $model = EmailCtl::getEmailModel();
      
      if( $model->prepare_reminder()) {
        if ($model->send()) {
          echo json_encode(
            array("message" => "Success")
          );
        } else {
          http_response_code(422);  
          echo json_encode(
            array("message" => "Unable to send email.")
          );          
        }
      } else{
            http_response_code(422);  
            echo json_encode(
              array("message" => "Unable to prepare email.")
            );
        }
      }

      public static function prepare_reminder(){

        $model = EmailCtl::getEmailModel();
        
        if( $model->prepare_reminder()) {
          echo json_encode(
            array(
              "html" => $model->body
            )
          );
        } else{
            http_response_code(422);  
            echo json_encode(
              array("message" => "Unable to prepare email.")
            );
        }
      }

      private static function getEmailModel() {
        $model = new \Models\Email();
        $data = json_decode(file_get_contents("php://input"));
        $idmember = $data->idmember;
        EmailCtl::transferParameters($data, $model);

        $memberstatus_model = new \Models\MembershipStatus();
        $status = $memberstatus_model->readOneFromIdmember($idmember);
        $model->goCardlessLink = isset($status['gocardlesslink'])?$status['gocardlesslink']:null;

        return $model;
      }

      private static function transferParameters($data, $model)
      {
        $returnValue= '';

        if (isset($data->toEmail)) {
          $model->toAddress = $data->toEmail;          
        }  else {
          $returnValue= "'To' email address missing";
        }
        if (isset($data->fromName)) {
          $model->fromName = $data->fromName;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= "'From' name is missing";
        }
        if (isset($data->fromTitle)) {
          $model->fromTitle = $data->fromTitle;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= "'From' title is missing";
        }
        if (isset($data->salutation)) {
          $model->salutation = $data->salutation;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= "Salutation missing";
        }
        if (isset($data->fromEmail)) {
          $model->fromAddress = $data->fromEmail;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= "'From' email address missing";
        }
        if (isset($data->subject)) {
          $model->subject = $data->subject;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= "Subject missing";
        }

        if (!empty($returnValue)) {
          http_response_code(422);  
          echo json_encode(
            array("message" => $returnValue)
          );
          exit(0);
        }     
        
    }
}