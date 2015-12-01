<?php

if (!defined('IN_DOIT')) {
	exit();
}

class NetModel extends Base{
	
	
	public static function reqDDMGServer($interface, $data){
		$conf = Controller::getConfig('conf');
		$ddmg_server_url = $conf['ddmg_server_url'];
		$ddmg_server_timeout = $conf['ddmg_server_timeout'];
		
		$curl_obj = Controller::instance('curl');
		
		$req_data = array(
				'caller' => 'ddmg_seller', 
				'callee' => 'ddmg_server',
				'eventid' => rand() % 10000, 
				'timestamp' => time(),
				'data' => $data
		);
		
		$ret = $curl_obj->postRequest($ddmg_server_url . $interface, json_encode($req_data), null, null, $ddmg_server_timeout);

		$ret_data = json_decode($ret, true);
		if(! $ret_data){
			Log::error('request ddmg_server error:' . $ret);
			return false;
		}
		return $ret_data;
	}
}