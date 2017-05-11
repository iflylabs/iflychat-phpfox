<?php

class Iflychat_Component_Controller_Messages extends Phpfox_Component 
{ 
    public function process() 
    {          
      $this->template()->setTitle('Messages'); 
      $suid2 = $this->request()->get('id'); 
      $aMessages = Phpfox::getService('iflychat')->getMessages($suid2);
      $anewMessages = array();
      foreach($aMessages as $aMessage) {
        $aMessage['timestamp'] = Phpfox::getLib('date')->convertTime((int)$aMessage['timestamp']); 
        $anewMessages[] = $aMessage;
      }      
      $this->template()->assign('aMessages', $anewMessages);
            
    } 
} 

?>