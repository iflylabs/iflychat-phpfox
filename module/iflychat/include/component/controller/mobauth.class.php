<?php

class Iflychat_Component_Controller_mobAuth extends Phpfox_Component 
{ 
    public function process() 
    {         
      header("Content-Type: text/html");
      $response = Phpfox::getService('iflychat')->mobAuth();
      echo $response;
      exit;
    } 
} 

?>