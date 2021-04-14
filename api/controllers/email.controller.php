<?php

namespace Controllers;

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

      private static function transferParameters($data, $model)
      {
        if (isset($data->to)) {
          $model->toAddress = $data->to->email;
          $model->toName = $data->to->name;
        }  else {
          http_response_code(422);  
          echo json_encode(
            array("message" => "No 'to' email address supplied")
          );
          exit(0);
        }
        if (isset($data->from)) {
          $model->fromAddress = $data->from->email;  
          $model->fromName = $data->from->name;
        } else {
          http_response_code(422);  
          echo json_encode(
            array("message" => "No 'to' email address supplied")
          );
          exit(0);
        }     
        $model->goCardlessLink = $data->goCardlessLink;
    }
}