<?php

namespace Controllers;

use DateTime;
use \Models\Members;
use \Models\MemberFilter;
use \Models\MembershipStatus;

/**
 * Controller to acomplish  tasks with multiple members
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-03-01
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class MembersCtl{

  public static function lifeAndHonorary(){  

    $model = new Members();

    echo json_encode($model->lifeAndHonorary(), JSON_NUMERIC_CHECK);
  }

  //$months is th enumber of months since last payment
  public static function lapsed(){  

    $model = new Members();

    $months = isset($_GET['months']) ? $_GET['months'] : 18;

    echo json_encode($model->lapsedMembers($months), JSON_NUMERIC_CHECK);
  }

  public static function cem(){  

    // set start and end of the period to examine
    // if no dates are provided then 
    //  - the end date defaults to a string representing today e.g. '2021-02-09'
    //  - the start date defaults to a string representing today minus 12 months 
    //    plus one day e.g. '2020-02-10'
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new Members();

    echo json_encode($model->contributingExMembers($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function discount(){  

    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new Members();

    echo json_encode($model->discountMembers($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function payingHonLife(){  
    
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new Members();

    echo json_encode($model->payingHonLifeMembers($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function duplicatepayers(){  
    
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    $start = isset($_GET['start']) ? $_GET['start'] : 
                (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');

    $model = new Members();

    echo json_encode($model->membersPayingTwice($start, $end), JSON_NUMERIC_CHECK);
  }

  public static function filter(){  

    $model = new MemberFilter();

    if (isset($_GET['surname']) && !empty($_GET['surname'])) {
          $model->setSurname($_GET['surname']);
    }
    if (isset($_GET['notsurname']) && !empty($_GET['notsurname'])) {
      $model->setNotSurname($_GET['notsurname']);
    }
    if (isset($_GET['businessname']) && !empty($_GET['businessname'])) {
      $model->setBusinessname($_GET['businessname']);
    }
    if (isset($_GET['businessorsurname']) && !empty($_GET['businessorsurname'])) {
      $model->setBusinessOrSurname($_GET['businessorsurname']);
    }
    if (isset($_GET['membertypeid']) && !empty($_GET['membertypeid'])) {
      $model->setMemberTypeID($_GET['membertypeid']);
    }
    if (isset($_GET['email1']) && !empty($_GET['email1'])) {
      $model->setEmail1($_GET['email1']);
    }
    if (isset($_GET['postonhold']) && !empty($_GET['postonhold'])) {
      $model->setPostOnHold($_GET['postonhold']);
    }
    if (isset($_GET['addressfirstline']) && !empty($_GET['addressfirstline'])) {
      $model->setAddressLineOne($_GET['addressfirstline']);
    }
    if (isset($_GET['paymentmethod']) && !empty($_GET['paymentmethod'])) {
      $model->setPaymentMethod($_GET['paymentmethod']);
    }
    if(isset($_GET['expirydatestart']) || isset($_GET['expirydateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['expirydatestart']) ? '' : $_GET['expirydatestart'], 
                                  !isset($_GET['expirydateend']) ? '' : $_GET['expirydateend']
                              );
  
      $model->setExpiryRange($start, $end);
    }
    if(isset($_GET['joindatestart']) || isset($_GET['joindateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['joindatestart']) ? '' : $_GET['joindatestart'], 
                                  !isset($_GET['joindateend']) ? '' : $_GET['joindateend']
                              );
  
      $model->setJoinRange($start, $end);
    }
    if(isset($_GET['reminderdatestart']) || isset($_GET['reminderdateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['reminderdatestart']) ? '' : $_GET['reminderdatestart'], 
                                  !isset($_GET['reminderdateend']) ? '' : $_GET['reminderdateend']
                              );
  
      $model->setReminderRange($start, $end);
    }
    if(isset($_GET['updatedatestart']) || isset($_GET['updatedateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['updatedatestart']) ? '' : $_GET['updatedatestart'], 
                                  !isset($_GET['updatedateend']) ? '' : $_GET['updatedateend']
                              );
  
      $model->setUpdateRange($start, $end);
    }
    if(isset($_GET['lasttransactiondatestart']) || isset($_GET['lasttransactiondateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['lasttransactiondatestart']) ? '' : $_GET['lasttransactiondatestart'], 
                                  !isset($_GET['lasttransactiondateend']) ? '' : $_GET['lasttransactiondateend']
                              );
  
      $model->setLastTransactionRange($start, $end);
    }

    $deleteDateFilterIsSet = false;
    if(isset($_GET['deletedatestart']) || isset($_GET['deletedateend'])) {
      $start='';
      $end='';
      list($start, $end) = $model->sanitizeDateValues(
                                  !isset($_GET['deletedatestart']) ? '' : $_GET['deletedatestart'], 
                                  !isset($_GET['deletedateend']) ? '' : $_GET['deletedateend']
                              );
  
      $model->setDeleteRange($start, $end);
      $deleteDateFilterIsSet = true;
  }

    // Normally you only want to view the un-deleted members
    // and that is the defaul setting.
    // So if "removed" is set to 0 or is missing then only
    // non-deleted members will appear in the list
    // if removed is set to 'any' then no filter applied
    if (isset($_GET['removed'])){
      if ($_GET['removed'] =='any') {    
        // no filter applied
      } else {
          if ($_GET['removed'] && ($_GET['removed'] == 'y' || $_GET['removed'] == 'yes' 
                                || $_GET['removed'] == 'true')) {
              $model->setDeleted();
          } else if ($deleteDateFilterIsSet) {
              // filter already applied
          } else {
              $model->setNotDeleted();
              
          }
      }
    } else if ($deleteDateFilterIsSet) {
        // filter already applied
    } else {
        $model->setNotDeleted();    
    }

    echo json_encode($model->execute(), JSON_NUMERIC_CHECK); //$model is of type MemberFilter
  }

  
  public static function patch(){
    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->method)){

        switch (strtolower($data->method)) {
            case 'anonymize':
                MembersCtl::anonymize();
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

  public static function anonymize(){  

    $filter_model = new MemberFilter();
    $filter_model->username = MembersCtl::username();


    $status_model = new MembershipStatus();
    $status_model->name = 'former';
    $status = $status_model->readOne();

    if ($status &&  $status['id']) {
      $filter_model->setMemberTypeID($status['id']);
    } else {
      http_response_code(501);   
      echo json_encode(
          array("message" => "No member status found with name like " . $status_model->name)
      );
      exit();
    }

    if(isset($_GET['deletedatestart']) || isset($_GET['deletedateend'])) {
      $start='';
      $end='';
      list($start, $end) = $filter_model->sanitizeDateValues(
                                  !isset($_GET['deletedatestart']) ? '' : $_GET['deletedatestart'], 
                                  !isset($_GET['deletedateend']) ? '' : $_GET['deletedateend']
                              );
  
      $filter_model->setDeleteRange($start, $end);
    } else {
      http_response_code(501);   
      echo json_encode(
          array("message" => "No delete dat range set!")
      );
      exit();
    }


  if($filter_model->anonymize()){
    echo '{';
        echo '"message": "Former members anonymized."';
    echo '}';
  }
  else{
      http_response_code(422);
      echo '{';
          echo '"message": "Unable to anonymize former members."';
      echo '}';
  }
}

private static function username(){
  $jwt = new \Models\JWTWrapper();
  return $jwt->user;
}

}

?>