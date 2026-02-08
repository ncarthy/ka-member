<?php

namespace Controllers;

use \Models\Country;

class CountryCtl{


  public static function read_all(){  

    echo json_encode(Country::getInstance()->read(), JSON_NUMERIC_CHECK);
  }


  public static function read_one($id){  

    echo json_encode(Country::getInstance()->setId($id)->readOne(), JSON_NUMERIC_CHECK);
  }


  public static function read_one_name($name){  

    echo json_encode(Country::getInstance()->setName($name)->readOne(), JSON_NUMERIC_CHECK);
  }

}