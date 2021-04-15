<?php

namespace Controllers;

use \Models\MembershipStatus;

class EmailCtl{


    public static function send_reminder($idmember){

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

      public static function prepare_reminder($idmember){

        $model = new \Models\Email();
        $data = json_decode(file_get_contents("php://input"));
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
        if (isset($data->to)) {
          $model->toAddress = $data->to->email;
          $model->salutation = $data->to->salutation;
        }  else {
          http_response_code(422);  
          echo json_encode(
            array("message" => "'To' email address or salutation missing")
          );
          exit(0);
        }
        if (isset($data->from)) {
          $model->fromAddress = $data->from->email;  
          $model->fromName = $data->from->name;
          $model->fromTitle = $data->from->title;
        } else {
          http_response_code(422);  
          echo json_encode(
            array("message" => "'From' email address, Name or Title missing")
          );
          exit(0);
        }     
        
    }
}