<?php

namespace Controllers;

/**
 * Controller to acomplish tasks with Membership Status
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-02-27
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class MembershipStatusCtl{

  /**
  *  Get all availabe membership statuses
  * <pre class="GET"> GET [url]/memberstatus</pre>
  *
  * @return JSON - **Array** of Membership Status **Objects**
  *
  * @since   2021-02-27
  * @author  Neil Carthy <neil.carthy42@gmail.com>
  **/
  public static function read_all(){  

    $model = new \Models\MembershipStatus();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }

  /**
  *  Get a single membership status
  * <pre class="GET"> GET [url]/memberstatus/:id</pre>
  *
  * @return JSON - **Array** of Membership Status **Objects**
  *
  * @since   2021-02-27
  * @author  Neil Carthy <neil.carthy42@gmail.com>
  **/
  public static function read_one($id){  

    $model = new \Models\MembershipStatus();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

    /**
  *  Get a single membership status
  * <pre class="GET"> GET [url]/memberstatus/:name</pre>
  *
  * @return JSON - **Array** of Membership Status **Objects**
  *
  * @since   2021-02-27
  * @author  Neil Carthy <neil.carthy42@gmail.com>
  **/
  public static function read_one_name($name){  

    $model = new \Models\MembershipStatus();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}