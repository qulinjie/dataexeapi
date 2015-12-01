<?php

define('EC_OK', 0);
define('EC_DB_CNT', 1);
define('EC_OTH_TKN', 2);
define('EC_NOT_LOGIN', 3);

define('EC_MTD_NON', 4);
define('EC_MOD_NON', 5);
define('EC_CTR_NON', 6);
define('EC_LIB_NON', 7);
define('EC_CNF_NON', 8);
define('EC_CNF_ERR', 9);
define('EC_VIW_NON', 10);
define('EC_MAL_NOT_VRF', 11);
define('EC_FLE_NON', 12);
define('EC_JSN_BAD', 13);
define('EC_PAR_BAD', 14);
define('EC_PRD_ACT', 15);

define('EC_OTH', 99);


define('EC_GEN_CDE', 101);
define('EC_SND_CDE', 102);
define('EC_SND_AGA', 103);
define('EC_NOT_VFY', 104);
define('EC_VFY_EPR', 105);
define('EC_USR_EST', 106);
define('EC_PWD_DEC', 107);
define('EC_ARD_LGN', 108);
define('EC_USR_ADD', 109);
define('EC_USR_NON', 110);
define('EC_PWD_WRN', 111);
define('EC_CHK_OUT', 112);
define('EC_PWD_EMP', 113);
define('EC_PWD_SAM', 114);
define('EC_PWD_UPD', 115);
define('EC_DAT_NON', 116);
define('EC_TEL_NON', 117);
define('EC_OPE_FAI', 118);
define('EC_DEL_FAI', 119);
define('EC_UPD_FAI', 120);
define('EC_ADD_FAI', 121);
define('EC_VER_NCH', 122);
define('EC_SIG_ARD', 123);

class EC extends Base {

	public static $_errMsg = array(
			
			EC_OK			=>	'success',
			EC_DB_CNT		=>	'connect to database failed',
			EC_OTH_TKN		=>	'token error, please retry later',
			EC_NOT_LOGIN	=>	'not login',
			EC_MTD_NON		=>	'method does not exists',
			EC_MOD_NON		=>	'mode does not exists',
			EC_CTR_NON		=>	'controller does not exists',
			EC_LIB_NON		=>	'library does not exists',
			EC_CNF_NON		=>	'configuration file does not exists',
			EC_CNF_ERR		=>	'configuration invalid',
			EC_MAL_NOT_VRF	=>	'the email is not verified',
			EC_OTH			=>	'other error',
			
			EC_FLE_NON		=>	'can not find corresponding file',
			EC_JSN_BAD		=>	'input is not json style',
			EC_PAR_BAD		=>	'input parameter error',
			EC_PRD_ACT		=>	'the same operation is in process',
			
			
			EC_GEN_CDE		=>	'genrate sms code fail',
			EC_SND_CDE		=>	'send sms code fail',
			EC_SND_AGA		=>	'do not send sms code too often',
			EC_CHK_OUT      =>  'sms code expired',
			EC_NOT_VFY		=>	'the telphone number is not verified',
			EC_VFY_EPR		=>	'the verification is expired',
			EC_USR_EST		=>	'the use already exists',
			EC_PWD_DEC		=>	'decode password fail',
			EC_ARD_LGN		=>	'a user login already, please logout before login another user',
			EC_USR_ADD		=>	'add user information fail',
			EC_USR_NON		=>	'user not exists',
			EC_PWD_WRN		=>	'wrong password',
			EC_PWD_EMP		=>	'password is empty',
			EC_PWD_SAM		=>	'same password',
			EC_PWD_UPD		=>	'update password fail',
			EC_DAT_NON		=>	'data not exists',
			EC_TEL_NON		=>	'dail failed',
			EC_OPE_FAI		=>	'operation failure',
			EC_DEL_FAI		=>	'delete row failure',
			EC_UPD_FAI		=>	'update row failure',
			EC_ADD_FAI		=>	'insert row failure',
			EC_VER_NCH		=>	'this is the highest versions',
			EC_SIG_ARD		=>	'you haved sign today,can not sign again',
	);
	public static function load(){
		return true;
	}
	public static function fail($errno, $unlock = true){
		$response_data = array(
				'caller' => doit::$caller,
				'callee' => doit::$callee,
				'timestamp' => time(),
				'eventid'	=>	doit::$eventid,
				'code' => $errno,
				'msg' => self::$_errMsg[$errno]
		);
		$response = json_encode($response_data);
		
		doit::$res = $response_data;
		doit::$res_str = $response;
		header('Content-type: application/json');
		echo $response;
		//if($unlock)$GLOBALS['processlock_obj']->unlock();
		exit(0);
	}
	
	public static function success($errno, $data = array(), $unlock = true){
		$response_data = array(
				'caller' => doit::$caller,
				'callee' => doit::$callee,
				'timestamp' => time(),
				'eventid'	=>	doit::$eventid,
				'code'	=> $errno,
				'msg'	=> self::$_errMsg[$errno],
		);
		if(! empty($data)){
			$response_data['data'] = $data;
		}
		$response = json_encode($response_data);
		
		doit::$res = $response_data;
		doit::$res_str = $response;
		header('Content-type: application/json');
		echo $response;
		//if($unlock)$GLOBALS['processlock_obj']->unlock();
		exit(0);
	}

	public static function fail_page($code, $return = false)
	{
		return self::error_page($code, self::$_errMsg[$code], $return);
	}
	
	public static function page_not_found($return = false)
	{
		return self::error_page(404, '页面没有找到', $return);
	}

	public static function error_page($code, $message, $return = false)
	{
		/*if($code < 100){
			header("Location: " . Router::getBaseUrl() . "index.php/view/error/" . $code);
			exit(0);
		}*/
		$view = View::getInstance();
		if($return)
			return $view->render('error/message', array('code' => $code, 'message' => $message), true);
		else $view->render('error/message', array('code' => $code, 'message' => $message));
		exit(0);
	}
	
}
