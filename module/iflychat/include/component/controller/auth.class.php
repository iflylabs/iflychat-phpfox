<?php

class Iflychat_Component_Controller_Auth extends Phpfox_Component 
{ 
    public function process() 
    {         
      header("Content-Type: application/json");
      $response = Phpfox::getService('iflychat')->ex_auth();
      echo json_encode($response);
      exit;
    } 
} 

?>