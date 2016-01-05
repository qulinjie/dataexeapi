<?php

class PayPasswordController extends Controller {

    public function handle($params = array(), $req_data = array()) {
        switch ($params[0]) {
			case "check":
				$this->check($req_data);
				break;
			case "passwordReset":
				$this->passwordReset($req_data);
				break;
			case "validatePassword":
				$this->validatePassword($req_data);
				break;
            default:
				Log::error('method not found .');
				EC::fail(EC_MTD_NON);
				break;
		}
	}

	private function check($req_data)
	{
		$session = self::instance('session');
		if(!$userInfo = $session->get('loginUser')){
			Log::error('check PayPassword not login');
			EC::fail(EC_NOT_LOGIN);
		}

		$status = $this->model('user')->checkPayPassword($userInfo['id']);
		EC::success(EC_OK,array('status' => $status));
	}

	private function passwordReset($req_data)
	{
		$session  = self::instance('session');
		if(!$userInfo = $session->get('loginUser')){
			Log::error('reset PayPassword not login');
			EC::fail(EC_NOT_LOGIN);
		}

		if(isset($req_data['oldPwd'])){
			$old = self::decrypt($req_data['oldPwd']);
			Log::notice('decrypt passwordReset <<<<<<<<<<'.var_export($old,true));
			$userBasicInfo = $this->model('user')->getUserBasicInfo($userInfo['id']);
			if(!password_verify($old,$userBasicInfo['pay_password'])){
				Log::error('payPassword reset oldPwd validate error');
				EC::fail(EC_PWD_WRN);
			}
		}

		$decrypted_pwd = self::decrypt($req_data['newPwd']);
		if(!$decrypted_pwd){
			Log::error('setPayPassword password decrypt error');
			EC::fail(EC_PWD_EMP);
		}

		$payPassword = password_hash($decrypted_pwd,PASSWORD_DEFAULT);
		if(!$payPassword){
			Log::error('setPayPassword password encrypt error');
			EC::fail(EC_OTH);
		}

		if(!$this->model('user')->updatePayPassword($userInfo['id'],$payPassword)){
			Log::error('setPayPassword is fail msg('.$this->model('user')->getErrorInfo().')');
			EC::fail(EC_UPD_REC);
		}

		MessageController::addMsg($userInfo['id'],300);
		EC::success(EC_OK);
	}

	private function validatePassword($req_data)
	{
		$session  = self::instance('session');
		if(!$userInfo = $session->get('loginUser')){
			Log::error('validatePassword PayPassword not login');
			EC::fail(EC_NOT_LOGIN);
		}

		$decrypted_pwd = self::decrypt($req_data['payPassword']);
		!$decrypted_pwd && EC::fail(EC_PWD_WRN);

		$encrypt_pwd = $this->model('user')->getPayPassword($userInfo['id']);
		!$encrypt_pwd && EC::fail(EC_PAR_BAD); //未设置支付，不让通过;

		if(password_verify($decrypted_pwd,$encrypt_pwd)){
			Log::notice('validatePayPassword is success id '.$userInfo['id']);
			EC::success(EC_OK,['status' => true]);
		}else{
			Log::error('validatePayPassword is fail id '.$userInfo['id']);
			EC::success(EC_OK,['status' => false]);
		}
	}

	public static function decrypt($pwd)
	{
		$decrypted_pwd = '';
		$privateKey  = openssl_pkey_get_private(self::getConfig('conf')['private_key']);
		$payPassword = base64_decode($pwd);
		openssl_private_decrypt($payPassword, $decrypted_pwd, $privateKey);
		return $decrypted_pwd;
	}
}
