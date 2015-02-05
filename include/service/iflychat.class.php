<?php 
  
class Iflychat_Service_Iflychat extends Phpfox_Service
{ 
    private function _get_user_id()
	{
		return (string)Phpfox::getUserId();
	}
	
	private function _get_user_name()
	{
		return Phpfox::getUserBy('full_name');
	}
  
  private function _get_user_username()
	{
		return Phpfox::getUserBy('user_name');
	}
  
  private function _get_user_friends() {
    $v = array();
    $arr = Phpfox::getService('friend')->get('friend.user_id = ' . Phpfox::getUserId());
    if(isset($arr[1])) {
      foreach($arr[1] as $usr) {
        $v[] = $usr['user_id'];
      }
    }
    return $v;
  }
  
	private $_base_url = '/';
	private $_module_url = 'module/iflychat/static';
	//private 
	private $_EXTERNAL_HOST;
	private $_EXTERNAL_PORT = '80';
	private $_EXTERNAL_A_HOST;
	private $_EXTERNAL_A_PORT = '443';
  
  private function _get_external_host() {
    //$this->_EXTERNAL_HOST = 'http://api' . (Phpfox::isParam('iflychat.ext_d_i'))?(Phpfox::getParam('iflychat.ext_d_i')):('') . '.iflychat.com';
    //echo Phpfox::getParam('iflychat.api_key');
    //exit;
    $this->_EXTERNAL_HOST = 'http://api' . $this->_get_ext_d_i() .'.iflychat.com';
    return $this->_EXTERNAL_HOST;
  }
  
  private function _get_external_a_host() {
    //$this->_EXTERNAL_A_HOST = 'https://api' . (Phpfox::isParam('iflychat.ext_d_i'))?(Phpfox::getParam('iflychat.ext_d_i')):('') . '.iflychat.com';
    $this->_EXTERNAL_A_HOST = 'https://api' . $this->_get_ext_d_i()  . '.iflychat.com';
    return $this->_EXTERNAL_A_HOST;
  }
private function _get_ext_d_i() {
    return Phpfox::getParam('iflychat.ext_d_i');
  }
  
	private function _return_pic_url() {
	  $avatarpath = Phpfox::getParam('core.url_user');
    $img_path = Phpfox::getUserBy('user_image');
    if(!empty($img_path)) {
      $avatarpath .= Phpfox::getUserBy('user_image');
      $avatarpath = str_replace("%s","_50_square",$avatarpath);
    }
    else {
      $avatarpath = Phpfox::getLib('url')->getDomain() . $this->_module_url . '/themes/' . Phpfox::getParam('iflychat.theme') .'/images/default_avatar.png';
    }
    return $avatarpath;
	}
	
	private function _iflychat_timer_read($name) 
	{
		global $timers;

		if (isset($timers[$name]['start'])) {
			$stop = microtime(TRUE);
			$diff = round(($stop - $timers[$name]['start']) * 1000, 2);

			if (isset($timers[$name]['time'])) {
					$diff += $timers[$name]['time'];
			}
			return $diff;
		}
		return $timers[$name]['time'];
	}

	private function _iflychat_timer_start($name) {
		global $timers;

		$timers[$name]['start'] = microtime(TRUE);
		$timers[$name]['count'] = isset($timers[$name]['count']) ? ++$timers[$name]['count'] : 1;
	}

	private function _iflychat_extended_http_request($url, array $options = array()) 
	{
			$result = new stdClass();
			// Parse the URL and make sure we can handle the schema.
			$uri = @parse_url($url);
			if ($uri == FALSE) {
				$result->error = 'unable to parse URL';
				$result->code = -1001;
				return $result;
			}

			if (!isset($uri['scheme'])) {
				$result->error = 'missing schema';
				$result->code = -1002;
				return $result;
			}

			$this->_iflychat_timer_start(__FUNCTION__);

			// Merge the default options.
			$options += array(
				'headers' => array(), 
				'method' => 'GET', 
				'data' => NULL, 
				'max_redirects' => 3, 
				'timeout' => 30.0, 
				'context' => NULL,
			);

			// Merge the default headers.
			$options['headers'] += array(
				'User-Agent' => 'Drupal (+http://api.iflychat.com/)',
			);

			// stream_socket_client() requires timeout to be a float.
			$options['timeout'] = (float) $options['timeout'];

			// Use a proxy if one is defined and the host is not on the excluded list.
			$proxy_server = '';
			if ($proxy_server && _drupal_http_use_proxy($uri['host'])) {
				// Set the scheme so we open a socket to the proxy server.
				$uri['scheme'] = 'proxy';
				// Set the path to be the full URL.
				$uri['path'] = $url;
				// Since the URL is passed as the path, we won't use the parsed query.
				unset($uri['query']);

				// Add in username and password to Proxy-Authorization header if needed.
				if ($proxy_username = '') {
					$proxy_password = '';
					$options['headers']['Proxy-Authorization'] = 'Basic ' . base64_encode($proxy_username . (!empty($proxy_password) ? ":" . $proxy_password : ''));
				}
				// Some proxies reject requests with any User-Agent headers, while others
				// require a specific one.
				$proxy_user_agent = '';
				// The default value matches neither condition.
				if ($proxy_user_agent === NULL) {
					unset($options['headers']['User-Agent']);
				}
				elseif ($proxy_user_agent) {
					$options['headers']['User-Agent'] = $proxy_user_agent;
				}
			}

		switch ($uri['scheme']) {
			case 'proxy':
			// Make the socket connection to a proxy server.
			$socket = 'tcp://' . $proxy_server . ':' . 8080;
			// The Host header still needs to match the real request.
			$options['headers']['Host'] = $uri['host'];
			$options['headers']['Host'] .= isset($uri['port']) && $uri['port'] != 80 ? ':' . $uri['port'] : '';
			break;

			case 'http':
			case 'feed':
				$port = isset($uri['port']) ? $uri['port'] : 80;
				$socket = 'tcp://' . $uri['host'] . ':' . $port;
				// RFC 2616: "non-standard ports MUST, default ports MAY be included".
				// We don't add the standard port to prevent from breaking rewrite rules
				// checking the host that do not take into account the port number.
				$options['headers']['Host'] = $uri['host'] . ($port != 80 ? ':' . $port : '');
			break;

			case 'https':
				// Note: Only works when PHP is compiled with OpenSSL support.
				$port = isset($uri['port']) ? $uri['port'] : 443;
				$socket = 'ssl://' . $uri['host'] . ':' . $port;
				$options['headers']['Host'] = $uri['host'] . ($port != 443 ? ':' . $port : '');
			break;

			default:
				$result->error = 'invalid schema ' . $uri['scheme'];
				$result->code = -1003;
			return $result;
		}

		if (empty($options['context'])) {
			$fp = @stream_socket_client($socket, $errno, $errstr, $options['timeout']);
		}
		else {
			// Create a stream with context. Allows verification of a SSL certificate.
			$fp = @stream_socket_client($socket, $errno, $errstr, $options['timeout'], STREAM_CLIENT_CONNECT, $options['context']);
		}

		// Make sure the socket opened properly.
		if (!$fp) {
			// When a network error occurs, we use a negative number so it does not
			// clash with the HTTP status codes.
			$result->code = -$errno;
			$result->error = trim($errstr) ? trim($errstr) : t('Error opening socket @socket', array('@socket' => $socket));

			// Mark that this request failed. This will trigger a check of the web
			// server's ability to make outgoing HTTP requests the next time that
			// requirements checking is performed.
			// See system_requirements().
			//variable_set('drupal_http_request_fails', TRUE);

			return $result;
		}

		// Construct the path to act on.
		$path = isset($uri['path']) ? $uri['path'] : '/';
		if (isset($uri['query'])) {
			$path .= '?' . $uri['query'];
		}

		// Only add Content-Length if we actually have any content or if it is a POST
		// or PUT request. Some non-standard servers get confused by Content-Length in
		// at least HEAD/GET requests, and Squid always requires Content-Length in
		// POST/PUT requests.
		$content_length = strlen($options['data']);
		if ($content_length > 0 || $options['method'] == 'POST' || $options['method'] == 'PUT') {
			$options['headers']['Content-Length'] = $content_length;
		}

		// If the server URL has a user then attempt to use basic authentication.
		if (isset($uri['user'])) {
			$options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (isset($uri['pass']) ? ':' . $uri['pass'] : ''));
		}

		// If the database prefix is being used by SimpleTest to run the tests in a copied
		// database then set the user-agent header to the database prefix so that any
		// calls to other Drupal pages will run the SimpleTest prefixed database. The
		// user-agent is used to ensure that multiple testing sessions running at the
		// same time won't interfere with each other as they would if the database
		// prefix were stored statically in a file or database variable.
		$test_info = &$GLOBALS['drupal_test_info'];
		if (!empty($test_info['test_run_id'])) {
			$options['headers']['User-Agent'] = drupal_generate_test_ua($test_info['test_run_id']);
		}

		$request = $options['method'] . ' ' . $path . " HTTP/1.0\r\n";
		foreach ($options['headers'] as $name => $value) {
			$request .= $name . ': ' . trim($value) . "\r\n";
		}
		$request .= "\r\n" . $options['data'];
		$result->request = $request;
		// Calculate how much time is left of the original timeout value.
		$timeout = $options['timeout'] - $this->_iflychat_timer_read(__FUNCTION__) / 1000;
		if ($timeout > 0) {
			stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
			fwrite($fp, $request);
		}

		// Fetch response. Due to PHP bugs like http://bugs.php.net/bug.php?id=43782
		// and http://bugs.php.net/bug.php?id=46049 we can't rely on feof(), but
		// instead must invoke stream_get_meta_data() each iteration.
		$info = stream_get_meta_data($fp);
		$alive = !$info['eof'] && !$info['timed_out'];
		$response = '';

		while ($alive) {
			// Calculate how much time is left of the original timeout value.
			$timeout = $options['timeout'] - $this->_iflychat_timer_read(__FUNCTION__) / 1000;
			if ($timeout <= 0) {
				$info['timed_out'] = TRUE;
				break;
			}
			stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
			$chunk = fread($fp, 1024);
			$response .= $chunk;
			$info = stream_get_meta_data($fp);
			$alive = !$info['eof'] && !$info['timed_out'] && $chunk;
		}
		fclose($fp);

		if ($info['timed_out']) {
			$result->code = HTTP_REQUEST_TIMEOUT;
			$result->error = 'request timed out';
			return $result;
		}
		// Parse response headers from the response body.
		// Be tolerant of malformed HTTP responses that separate header and body with
		// \n\n or \r\r instead of \r\n\r\n.
		list($response, $result->data) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
		$response = preg_split("/\r\n|\n|\r/", $response);

		// Parse the response status line.
		list($protocol, $code, $status_message) = explode(' ', trim(array_shift($response)), 3);
		$result->protocol = $protocol;
		$result->status_message = $status_message;

		$result->headers = array();

		// Parse the response headers.
		while ($line = trim(array_shift($response))) {
			list($name, $value) = explode(':', $line, 2);
			$name = strtolower($name);
			if (isset($result->headers[$name]) && $name == 'set-cookie') {
				// RFC 2109: the Set-Cookie response header comprises the token Set-
				// Cookie:, followed by a comma-separated list of one or more cookies.
				$result->headers[$name] .= ',' . trim($value);
			}
			else {
				$result->headers[$name] = trim($value);
			}
		}

		$responses = array(
			100 => 'Continue', 
			101 => 'Switching Protocols', 
			200 => 'OK', 
			201 => 'Created', 
			202 => 'Accepted', 
			203 => 'Non-Authoritative Information', 
			204 => 'No Content', 
			205 => 'Reset Content', 
			206 => 'Partial Content', 
			300 => 'Multiple Choices', 
			301 => 'Moved Permanently', 
			302 => 'Found', 
			303 => 'See Other', 
			304 => 'Not Modified', 
			305 => 'Use Proxy', 
			307 => 'Temporary Redirect', 
			400 => 'Bad Request', 
			401 => 'Unauthorized', 
			402 => 'Payment Required', 
			403 => 'Forbidden', 
			404 => 'Not Found', 
			405 => 'Method Not Allowed', 
			406 => 'Not Acceptable', 
			407 => 'Proxy Authentication Required', 
			408 => 'Request Time-out', 
			409 => 'Conflict', 
			410 => 'Gone', 
			411 => 'Length Required', 
			412 => 'Precondition Failed', 
			413 => 'Request Entity Too Large', 
			414 => 'Request-URI Too Large', 
			415 => 'Unsupported Media Type', 
			416 => 'Requested range not satisfiable', 
			417 => 'Expectation Failed', 
			500 => 'Internal Server Error', 
			501 => 'Not Implemented', 
			502 => 'Bad Gateway', 
			503 => 'Service Unavailable', 
			504 => 'Gateway Time-out', 
			505 => 'HTTP Version not supported',
		);
		// RFC 2616 states that all unknown HTTP codes must be treated the same as the
		// base code in their class.
		if (!isset($responses[$code])) {
			$code = floor($code / 100) * 100;
		}
		$result->code = $code;

		switch ($code) {
			case 200: // OK
			case 304: // Not modified
			break;
			case 301: // Moved permanently
			case 302: // Moved temporarily
			case 307: // Moved temporarily
				$location = $result->headers['location'];
				$options['timeout'] -= $this->_iflychat_timer_read(__FUNCTION__) / 1000;
				if ($options['timeout'] <= 0) {
					$result->code = HTTP_REQUEST_TIMEOUT;
					$result->error = 'request timed out';
				}
				elseif ($options['max_redirects']) {
					// Redirect to the new location.
					$options['max_redirects']--;
					$result = iflychat_extended_http_request($location, $options);
					$result->redirect_code = $code;
				}
				if (!isset($result->redirect_url)) {
					$result->redirect_url = $location;
				}
			break;
			default:
				$result->error = $status_message;
		}

		return $result;
	}
	
	public function loadSettings() 
  { 
    if($this->_verify_access()) {
      $my_settings = array(
			  'uid' => $this->_get_user_id(),
			  'username' => $this->_get_user_name(),
			  'current_timestamp' => time(),
			  'polling_method' => "3",
			  'pollUrl' => " ",
			  'sendUrl' => " ",
			  'statusUrl' => " ",
			  'status' => "1",
			  'goOnline' => 'Go Online',
			  'goIdle' => 'Go Idle',
			  'newMessage' => Phpfox::getPhrase('iflychat.newmessage'),
			  'images' => Phpfox::getLib('url')->getDomain() . $this->_module_url . '/themes/' . Phpfox::getParam('iflychat.theme') . '/images/',
			  'sound' => Phpfox::getLib('url')->getDomain() . $this->_module_url . '/swf/sound.swf',
			  'soundFile' => Phpfox::getLib('url')->getDomain() . $this->_module_url . '/wav/notification.mp3',
			  'noUsers' => "<div class=\"item-list\"><ul><li class=\"drupalchatnousers even first last\">No users online</li></ul></div>",
			  'smileyURL' => Phpfox::getLib('url')->getDomain() . $this->_module_url . '/smileys/very_emotional_emoticons-png/png-32x32/',
			  'addUrl' => " ",
			  'notificationSound' => (Phpfox::getParam('iflychat.notification_sound'))?"1":"2",
			  'basePath' => Phpfox::getLib('url')->getDomain(),
			  'useStopWordList' => $this->_get_use_stop_word_list(),
			  'blockHL' => $this->_get_stop_links(),
			  'allowAnonHL' => (Phpfox::getParam('iflychat.allow_anon_links'))?('1'):('2'),
			  'iup' => (Phpfox::getParam('iflychat.user_picture'))?'1':'2',
			  'open_chatlist_default' => (!Phpfox::getParam('iflychat.minimize_chat_user_list'))?'1':'2',
			  'admin' => Phpfox::isAdmin()?'1':'0',
			  'exurl' => Phpfox::getLib('url')->makeUrl('iflychat.auth'),
              'renderImageInline' => (Phpfox::getParam('iflychat.allow_render_images'))?'1':'2',
	      'searchBar' => (Phpfox::getParam('iflychat.enable_search_bar'))?'1':'2',
              'mobileWebUrl' => Phpfox::getLib('url')->makeUrl('iflychat.mobauth'),
              'theme' => Phpfox::getParam('iflychat.theme'),
              'chat_type' => '2'
		  );

      if($this->_get_use_stop_word_list()!="1") {
        $my_settings['stopWordList'] = Phpfox::getParam('iflychat.stop_word_list');
      }      
      
	    if($my_settings['iup'] == '1') {
        $my_settings['up'] = $this->_return_pic_url();
	      $my_settings['default_up'] = Phpfox::getLib('url')->getDomain() . $this->_module_url . '/themes/' . Phpfox::getParam('iflychat.theme') .'/images/default_avatar.png';
	      $my_settings['default_cr'] = Phpfox::getLib('url')->getDomain() . $this->_module_url . '/themes/' . Phpfox::getParam('iflychat.theme')  . '/images/default_room.png';
      }
	    
      $my_settings['upl'] = Phpfox::getLib('url')->makeUrl('profile', $this->_get_user_username());
      $is_https = Phpfox::getLib('request')->getServer('HTTPS');
	    if(!empty($is_https)) {
        $my_settings['external_host'] = $this->_get_external_a_host();
        $my_settings['external_port'] = $this->_EXTERNAL_A_PORT;
        $my_settings['external_a_host'] = $this->_get_external_a_host();
        $my_settings['external_a_port'] = $this->_EXTERNAL_A_PORT;		
	    }
	    else {
	      $my_settings['external_host'] = $this->_get_external_host();
        $my_settings['external_port'] = $this->_EXTERNAL_PORT;
		    $my_settings['external_a_host'] = $this->_get_external_host();
        $my_settings['external_a_port'] = $this->_EXTERNAL_PORT;
	    }
	  
	    $my_settings['text_currently_offline'] = str_replace('{drupalchat_user}', 'drupalchat_user', Phpfox::getPhrase('iflychat.text_currently_offline'));
      $my_settings['text_is_typing'] = str_replace('{user}', 'user', Phpfox::getPhrase('iflychat.text_is_typing'));	
	    $my_settings['text_close'] = Phpfox::getPhrase('iflychat.text_close');
	    $my_settings['text_minimize'] = Phpfox::getPhrase('iflychat.text_minimize');
	    $my_settings['text_mute'] = Phpfox::getPhrase('iflychat.text_mute');
	    $my_settings['text_unmute'] = Phpfox::getPhrase('iflychat.text_unmute');
	    $my_settings['text_available'] = Phpfox::getPhrase('iflychat.text_available');
	    $my_settings['text_idle'] = Phpfox::getPhrase('iflychat.text_idle');
	    $my_settings['text_busy'] = Phpfox::getPhrase('iflychat.text_busy');
	    $my_settings['text_offline'] = Phpfox::getPhrase('iflychat.text_offline');
	    $my_settings['text_lmm'] = Phpfox::getPhrase('iflychat.text_lmm');
      $my_settings['text_nmm'] = Phpfox::getPhrase('iflychat.text_nmm');
      $my_settings['text_clear_room'] = Phpfox::getPhrase('iflychat.text_clear_room');
	    if(Phpfox::isAdmin()) {
        $my_settings['admin'] = "1";
        $my_settings['arole'] = $this->getUserRoles();
		    $my_settings['text_ban'] = Phpfox::getPhrase('iflychat.text_ban');
		    $my_settings['text_kick'] = Phpfox::getPhrase('iflychat.text_kick');
		    $my_settings['text_ban_window_title'] = Phpfox::getPhrase('iflychat.text_ban_window_title');
		    $my_settings['text_ban_window_default'] = Phpfox::getPhrase('iflychat.text_ban_window_default');
		    $my_settings['text_ban_window_loading'] = Phpfox::getPhrase('iflychat.text_ban_window_loading');
		    $my_settings['text_manage_rooms'] = Phpfox::getPhrase('iflychat.text_manage_rooms');
		    $my_settings['text_unban'] = Phpfox::getPhrase('iflychat.text_unban');
		    $my_settings['text_unban_ip'] = Phpfox::getPhrase('iflychat.text_unban_ip');
      }
      else {
        $my_settings['admin'] = "0";
      }
		  //Phpfox::getParam('iflychat.api_key') 
		  return $my_settings;
    
    }
    else {
      return false;
    }
	}
  
	public function ex_auth()
	{
		if($this->_verify_access()) {
      $json = (array)$this->_get_auth();
		  $json['uname'] = $this->_get_user_name();
		  $json['uid'] = $this->_get_user_id();
		  return $json;
}
	}

	public function mobAuth()
	{
		$is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
  	$data = array('settings' => array());
  	$data['settings']['authUrl'] = Phpfox::getLib('url')->makeUrl('iflychat.auth');
  	$data['settings']['host'] = (($is_https)?($this->_get_external_a_host()):($this->_get_external_host()));
  	$data['settings']['port'] = (($is_https)?($this->_EXTERNAL_A_PORT):($this->_EXTERNAL_PORT));
  	$data = json_encode($data);
  	$options = array(
  		'method' => 'POST',
  		'data' => $data,
  		'timeout' => 15,
  		'headers' => array('Content-Type' => 'application/json'),
  	);
 		$result = $this->_iflychat_extended_http_request($this->_get_external_a_host() . ':' . $this->_EXTERNAL_A_PORT .  '/m/v1/app', $options);
  	if($result->code == 200) {
    	//$result = json_decode($result, true);
    	return $result->data;
  	}
  	else {
    	return null;
    }
	}

	private function _get_auth()
	{
		if(Phpfox::isAdmin()) {
			$role = "admin";
		}
		else {
			$role = array();
		}
  
  $data = array(
    'uname' => $this->_get_user_name(),
    'uid' => $this->_get_user_id(),
    'api_key' => Phpfox::getParam('iflychat.api_key'),
	  'image_path' => Phpfox::getLib('url')->getDomain() . $this->_module_url . '/themes/' . Phpfox::getParam('iflychat.theme') . '/images',
	  'isLog' => TRUE,
	  'whichTheme' => 'blue',
	  'enableStatus' => TRUE,
	  'role' => $role,
	  'validState' => array('available','offline','busy','idle'),
	  'rel' => (Phpfox::getParam('iflychat.enable_friends'))?('1'):('0'),
  );
//Sends user roles if user is admin
    if($role=='admin') {
        $data['allRoles'] = $this->getUserRoles();
    }
  if(Phpfox::getParam('iflychat.enable_friends')) {
    $final_list = array();
    $final_list['1']['name'] = 'friend';
    $final_list['1']['plural'] = 'friends';
    $final_list['1']['valid_uids'] = $this->_get_user_friends();
    $data['valid_uids'] = $final_list;
  }
  if(Phpfox::getParam('iflychat.user_picture')) {
    $data['up'] = $this->_return_pic_url();
  }
  $data['upl'] = Phpfox::getLib('url')->makeUrl('profile', $this->_get_user_username());
  /*
  if(variable_get('drupalchat_rel', DRUPALCHAT_REL_AUTH) > DRUPALCHAT_REL_AUTH) {
    $new_valid_uids = _drupalchat_get_buddylist($user->uid);
    if(!isset($_SESSION['drupalchat_valid_uids']) || ($_SESSION['drupalchat_valid_uids'] != $new_valid_uids)) {
      $data['valid_uids'] = $new_valid_uids;
	  $_SESSION['drupalchat_valid_uids'] = $new_valid_uids;
    }
	else {
	  $data['valid_uids'] = $new_valid_uids;
	}
  }
  */
  //drupal_set_message(print_r(user_relationships_types_load(), true));
  $data = json_encode($data);
  $options = array(
    'method' => 'POST',
    'data' => $data,
    'timeout' => 15,
    'headers' => array('Content-Type' => 'application/json'),
  );
  
  $result = $this->_iflychat_extended_http_request($this->_get_external_a_host() . ':' . $this->_EXTERNAL_A_PORT .  '/p/', $options);
  
  if($result->code == 200) {
    $result = json_decode($result->data);
   if(Phpfox::getLib('setting')->isParam('iflychat.ext_d_i') && $result->_i != Phpfox::getParam('iflychat.ext_d_i')) {
        // echo Phpfox::getParam('iflychat.ext_d_i')."-";
        // Phpfox::getLib('setting')->setParam('iflychat.ext_d_i', $result->_i);
        $set = array('value_actual' => $result->_i);
        $aRow = Phpfox::getLib('database')->update('phpfox_setting', $set, '`var_name` = \'ext_d_i\'');
        // echo $result->_i;
        Phpfox::getLib('cache')->remove('setting');	  
}
    return $result;
  }
  else {
    return null;
  }
	}
  
  public function saveSettings() 
  {
    $data = array(
      'api_key' => Phpfox::getParam('iflychat.api_key'),
	    'enable_chatroom' => (Phpfox::getParam('iflychat.public_chatroom'))?('1'):('2'),
	    'theme' => Phpfox::getParam('iflychat.theme'),
	    'notify_sound' => (Phpfox::getParam('iflychat.notification_sound'))?('1'):('2'),
	    'smileys' => (Phpfox::getParam('iflychat.enable_smileys'))?('1'):('2'),
	    'log_chat' => (Phpfox::getParam('iflychat.log_chat'))?('1'):('2'),
	    'chat_topbar_color' => Phpfox::getParam('iflychat.chat_top_bar_color'),
	    'chat_topbar_text_color' => Phpfox::getParam('iflychat.chat_top_bar_text_color'),
	    'font_color' => Phpfox::getParam('iflychat.chat_font_color'),
	    'chat_list_header' => Phpfox::getParam('iflychat.chat_list_header'),
	    'public_chatroom_header' => Phpfox::getParam('iflychat.public_chatroom_header'),
	    'rel' => '0',
	    'version' => 'PHPFox-1.0.8',
	    'show_admin_list' => '2',
      'clear' => $this->_get_allow_single_message_delete(),
      'delmessage' => $this->_get_allow_clear_room_history(),
	    'ufc' => (Phpfox::getParam('iflychat.allow_user_font_color'))?('1'):('2'),
      'guest_prefix' => Phpfox::getParam('iflychat.anon_prefix'),
      'enable_guest_change_name' => (Phpfox::getParam('iflychat.anon_change_name'))?('1'):('2'),
'file_attachment' => (Phpfox::getParam('iflychat.enable_file_attachment'))?('1'):('2'),
      'mobile_browser_app' => (Phpfox::getParam('iflychat.enable_mobile_browser_app'))?('1'):('2'),
    );
    //print_r($data);
    $data = json_encode($data);
    $options = array(
      'method' => 'POST',
      'data' => $data,
      'timeout' => 15,
      'headers' => array('Content-Type' => 'application/json'),
    );
    $key = Phpfox::getParam('iflychat.api_key');
    if(!empty($key)) {
      $result = $this->_iflychat_extended_http_request($this->_get_external_a_host() . ':' . $this->_EXTERNAL_A_PORT .  '/z/', $options);
      if($result->code != 200) {
	      Phpfox_Error::set("Unable to connect to iFlyChat server. Error code - " . $result->code . ". Error message - " . $result->error . ". Please contact our support team at iflychat.com.");
	    }
    }
  }
  
  public function getInbox() {
    $query = array();
    if($this->_verify_access()) {
      $data = array('uid' => $this->_get_user_id(),'api_key' => Phpfox::getParam('iflychat.api_key'),);
      $data = json_encode($data);
      $options = array(
        'method' => 'POST',
        'data' => $data,
        'timeout' => 15,
        'headers' => array('Content-Type' => 'application/json'),
      );     
      $result = $this->_iflychat_extended_http_request($this->_get_external_a_host() . ':' . $this->_EXTERNAL_A_PORT . '/r/', $options);
      $query = json_decode($result->data, true); 
    }  
    return $query;    
  }
  
  public function getMessages($uid2) {
    $query = array();
    if($this->_verify_access()) {
      $data = array('uid1' => $this->_get_user_id(), 'uid2' => $uid2, 'api_key' => Phpfox::getParam('iflychat.api_key'),);
      $data = json_encode($data);
      $options = array(
        'method' => 'POST',
        'data' => $data,
        'timeout' => 15,
        'headers' => array('Content-Type' => 'application/json'),
     );     
      $result = $this->_iflychat_extended_http_request($this->_get_external_a_host() . ':' . $this->_EXTERNAL_A_PORT . '/q/', $options);
      $query = json_decode($result->data, true);
    } 
    return $query;          
  }
  
  private function _verify_access() {
    if ($this->_get_user_id() == 0)  {
      return false;
    }
    $key = Phpfox::getParam('iflychat.api_key');
    if(empty($key)) {
      return false;
    }
    return true;
  }
  
  private function _get_allow_single_message_delete() {
    $v = Phpfox::getParam('iflychat.allow_single_message_delete');
    if($v == "Allow all users") {
      return '1';
    }
    else if($v == "Allow only moderators") {
      return '2';
    }
    else if($v == "Disable") {
      return '3';
    }
    else {
      return '1';
    }
  }
  
  private function _get_allow_clear_room_history() {
    $v = Phpfox::getParam('iflychat.allow_clear_room_history');
    if($v == "Allow all users") {
      return '1';
    }
    else if($v == "Allow only moderators") {
      return '2';
    }
    else if($v == "Disable") {
      return '3';
    }
    else {
      return '1';
    }
  }
  
  private function _get_use_stop_word_list() {
    $v = Phpfox::getParam('iflychat.use_stop_word_list');
    if($v == "Don't Filter") {
      return '1';
    }
    else if($v == "Filter in public chatroom") {
      return '2';
    }
    else if($v == "Filter in private chats") {
      return '3';
    }
    else if($v == "Filter in all rooms") {
      return '4';
    }
    else {
      return '1';
    }
  }
  
  private function _get_stop_links() {
    $v = Phpfox::getParam('iflychat.stop_links');
    if($v == "Don't block") {
      return '1';
    }
    else if($v == "Block in public chatroom") {
      return '2';
    }
    else if($v == "Block in private chats") {
      return '3';
    }
    else if($v == "Block in all rooms") {
      return '4';
    }
    else {
      return '1';
    }
  }
private function _get_path_visibility() {
    $v = Phpfox::getParam('iflychat.path_visibility');
    if($v == "Only the listed pages") {
      return '2';
    }
    else {
      return '1';
    }
  }

  public function pathCheck() {
    $page_match = FALSE;
    if (trim(Phpfox::getParam('iflychat.path_pages')) !== '')
    {
      if(function_exists('mb_strtolower')) {
        $pages = mb_strtolower(Phpfox::getParam('iflychat.path_pages'));
        $path = mb_strtolower($_SERVER['REQUEST_URI']);
      }
      else {
        $pages = strtolower(Phpfox::getParam('iflychat.path_pages'));
        $path = strtolower($_SERVER['REQUEST_URI']);
      }
      $page_match = $this->_match_path($path, $pages);
      $page_match = ($this->_get_path_visibility() === '1')?(!$page_match):$page_match;
    }
    else if($this->_get_path_visibility() == '1'){
      $page_match = TRUE;
    }
    return $page_match;
  }
  
  private function _match_path($path, $patterns) {
    $to_replace = array(
      '/(\r\n|\n)/',
      '/\\\\\*/',
    );
    $replacements = array(
      '|',
      '.*',
    );
    $patterns_quoted = preg_quote($patterns, '/');
    $regexps = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';
    return (bool) preg_match($regexps, $path);
  }
  private function getUserRoles(){
      $arr = Phpfox::getService('user.group')->getAll();
      $roleArr=array();
      for($i=0;$i<sizeof($arr);$i++){
          $roleArr +=  array($arr[$i]['user_group_id'] => $arr[$i]['title']);

      }

      return $roleArr;
  }
} 
  
?>