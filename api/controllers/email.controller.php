<?php

namespace Controllers;

use \Models\MembershipStatus;

class EmailCtl{


    public static function send_reminder(){

        $model = new \Models\Email();
        $data = json_decode(file_get_contents("php://input"));
        EmailCtl::transferParameters($data, $model);
        
        if( $model->send_reminder()) {
          echo json_encode(
            array(
              "message" => "Email sent."
            )
          );
        } else{
            http_response_code(422);  
            echo json_encode(
              array("message" => "Unable to send email.")
            );
        }
      }

      public static function prepare_reminder(){

        $model = new \Models\Email();
        $data = json_decode(file_get_contents("php://input"));
        $idmember = $data->idmember;
        EmailCtl::transferParameters($data, $model);

        $memberstatus_model = new \Models\MembershipStatus();
        $status = $memberstatus_model->readOneFromIdmember($idmember);
        $model->goCardlessLink = isset($status['goCardlessLink'])?$status['goCardlessLink']:null;
        
        if( $model->prepare_reminder()) {
          /*echo json_encode(
            array(
              "html" => $model->body
            )
          );*/
          echo $model->body;
        } else{
            http_response_code(422);  
            echo json_encode(
              array("message" => "Unable to prepare email.")
            );
        }
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
          $returnValue .= " 'From' name is missing";
        }
        if (isset($data->fromTitle)) {
          $model->fromTitle = $data->fromTitle;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= " 'From' title is missing";
        }
        if (isset($data->salutation)) {
          $model->salutation = $data->salutation;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= " Salutation missing";
        }
        if (isset($data->fromAddress)) {
          $model->fromAddress = $data->fromAddress;          
        }  else {
          if (!empty($returnValue)) { $returnValue .= '; '; }
          $returnValue .= " 'From' email address missing";
        }

        if (!isset($returnValue)) {
          http_response_code(422);  
          echo json_encode(
            array("message" => $returnValue)
          );
          exit(0);
        }     
        
    }
}