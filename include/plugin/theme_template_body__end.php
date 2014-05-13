<?php

$settings = Phpfox::getService('iflychat')->loadSettings();

if($settings!=false) {
  if(!Phpfox::isAdminPanel()) {
    echo '<script src="' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/ba-emotify.js"></script>';
    echo '<script src="' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/jquery.titlealert.min.js"></script>';
    echo '<script src="' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/script.js"></script>';
  }
  else {
    echo '<script type = "text/javascript">window.onload = function() {var _body = document.getElementsByTagName(\'body\') [0];var s = document.createElement("script");s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/ba-emotify.js";_body.appendChild(s);s = document.createElement("script");s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/jquery.titlealert.min.js";_body.appendChild(s);s = document.createElement("script");s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/script.js";_body.appendChild(s);};</script>';
  }
    echo '<script>var Drupal={};Drupal.settings={};Drupal.settings.drupalchat=' . json_encode($settings) . ';</script>';
}

?>