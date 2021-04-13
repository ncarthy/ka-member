<?php

namespace Controllers;

class EmailCtl{


    public static function send(){

        $model = new \Models\Email();
        $data = json_decode(file_get_contents("php://input"));
        //EmailCtl::transferParameters($data, $model);
        
        if( $model->send()) {
          echo json_encode(
            array(
              "message" => "Email sent."
            )
          , JSON_NUMERIC_CHECK);
        } else{
          // if unable to create the model, tell the admin
            http_response_code(422);  
            echo json_encode(
              array("message" => "Unable to send email.")
            );
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
        }
    }
}