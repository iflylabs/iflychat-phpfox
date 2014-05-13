<?php
  if(($this->request()->get('module-id') == "iflychat")||($this->request()->get('product-id') == "iflychat")) {
    Phpfox::getService('iflychat')->saveSettings();
  }

?>