<?php

class Iflychat_Component_Controller_Inbox extends Phpfox_Component 
{ 
    public function process() 
    {          
      $this->template()->setTitle('Inbox'); 
      $aThreads = Phpfox::getService('iflychat')->getInbox();
      $anewThreads = array();
      foreach($aThreads as $aThread) {
        $aThread['timestamp'] = Phpfox::getLib('date')->convertTime((int)$aThread['timestamp']); 
        $anewThreads[] = $aThread;
      }      
      $sThreadUrl = Phpfox::getLib('url')->makeUrl('iflychat.messages');
      $this->template()->assign(array('sThreadUrl' => $sThreadUrl,));
      $this->template()->assign('aThreads', $anewThreads);
            
    } 
} 

?>