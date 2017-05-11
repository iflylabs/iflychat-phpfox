<?php

$settings = Phpfox::getService('iflychat')->loadSettings();
if($settings!=false) {
	if($settings['visible'] == '1'){
		echo '<script src="' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/iflychat-popup.js"></script>';
	}
    echo '<script type = "text/javascript">window.onload = function() {
    	var _body = document.getElementsByTagName(\'body\') [0];
    	var s = document.createElement("script");
    	s.type = "text/javascript";
    	s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/ba-emotify.js";
    	_body.appendChild(s);
    	s = document.createElement("script");
    	s.type = "text/javascript";
    	s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/jquery.titlealert.min.js";
    	_body.appendChild(s);
    	s = document.createElement("script");
    	s.type = "text/javascript";
    	s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/script.js";_body.appendChild(s);
    	s = document.createElement("script");
    	s.type = "text/javascript";
    	s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/iflychat.js";_body.appendChild(s);
    };</script>';
    if(Phpfox::getLib('url')->getFullUrl() == Phpfox::getLib('url')->getDomain().'index.php?do=/admincp/setting/edit/product-id_iflychat/'){
		echo '<script type = "text/javascript">window.onload = function() {
			var _body1 = document.getElementsByClassName(\'main_title_holder\') [0];
			var s1 = document.createElement("div");
			s1.id = "main_title_holder";
			s1.style.marginLeft = "100px";
			_body1.appendChild(s1);
			var s2 = document.createElement("a");
			s2.innerHTML = "APP Settings";
			s2.setAttribute("href", "' . $settings['dashboardUrl'] . '");
			s2.setAttribute("target", "_blank");
			var s3 = document.createElement("h1");
			s3.appendChild(s2);
			s1.appendChild(s3);
			var _body1 = document.getElementsByClassName(\'main_title_holder\') [0];
			var s1 = document.createElement("div");
			s1.id = "main_title_holder";
			s1.style.marginLeft = "240px";
			_body1.appendChild(s1);
			var s2 = document.createElement("a");
			s2.innerHTML = "Show Embed Chat";
			s2.setAttribute("href", "https://iflychat.com/embedded-chatroom-example-public-chatroom");
			s2.setAttribute("target", "_blank");
			var s3 = document.createElement("h1");
			s3.appendChild(s2);
			s1.appendChild(s3);
			var _body = document.getElementsByTagName(\'body\') [0];
			var s = document.createElement("script");
			s.type = "text/javascript";
			s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/ba-emotify.js";
			_body.appendChild(s);
			s = document.createElement("script");
			s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/jquery.titlealert.min.js";
			_body.appendChild(s);
			s = document.createElement("script");
			s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/script.js";
			_body.appendChild(s);
			s = document.createElement("script");
			s.type = "text/javascript";s.src = "' . Phpfox::getLib('url')->getDomain() . 'module/iflychat/static/js/iflychat.js";
			_body.appendChild(s);
			var sel = document.getElementsByTagName(\'select\')[3];
			sel.id = "sel";
			var table_header2 = document.getElementsByClassName(\'table_header2\') [5];
			table_header2.id = "tab1";
			var table3 = document.getElementsByClassName(\'table3\') [5];
			table3.id = "tab2";
			jQuery(document).ready(function($) {
			  $("#sel").change(function() {
			    if (($("#sel").val() == "Only the listed pages") || ($("#sel").val() == "Everywhere except those listed")) {
				  $("#tab1").show();
				  $("#tab2").show();
				}
				else {
				  $("#tab1").hide();
				  $("#tab2").hide();
				}
			  });
			  $("#sel").change();
			});
		};</script>';
	}
    echo '<script>var Drupal={};Drupal.settings={};Drupal.settings.drupalchat=' . json_encode($settings) . ';</script>';
}

?>