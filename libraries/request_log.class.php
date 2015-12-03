<?php
if (!defined('IN_DOIT')) {
	exit();
}

/**
 * 记录每一个请求
 * @author paco
 *
 */

class request_log extends Base {
	
	private $url;
	private $post_data;
	
	function __construct($url, $post_data) {
		
		$this->url = $url;
		$this->post_data = $post_data;
		Log::notice('');
		Log::notice('***************');
		Log::notice('request_data[' . $this->url . ']' . $this->post_data);
		$session_obj = Controller::instance('session');
		$session_obj->log();
		$cookie_obj = Controller::instance('cookie');
		$cookie_obj->log();
	}
	
	function __destruct() {
		Log::notice('response_data[' . $this->url . ']' . doit::$res_str);
		$session_obj = Controller::instance('session');
		$session_obj->log();
		$cookie_obj = Controller::instance('cookie');
        $cookie_obj->log();
        Log::notice('***************');
        Log::notice('');
	}
}