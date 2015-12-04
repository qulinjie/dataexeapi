<?php

class UserController extends Controller {
    
    public function handle($params = array(), $req_data = array()) {
        switch ($params[0]) {
            case "send_sms_code":
                $this->sendSmsCode($req_data);
                break;
            case "register":
                $this->register($req_data);
                break;
            default:
				Log::error('method not found .');
				EC::fail(EC_MTD_NON);
				break;
		}	
	}

	private function register($req_data){
	    $tel = $req_data['tel'];
	    $pwd = $req_data['pwd'];
	    $code = $req_data['code'];
	    
	    if(!$tel || !$pwd || !$code){
	        Log::error('request param error!');
	        EC::fail(EC_PAR_BAD);
	    }
	    
	    //确保手机号验证过并且未过期
	    $verify_model = $this->model('verify');
	    $verify_data = $verify_model->getVerifyRecordByTel($tel,$code);
	    //是否验证通过
	    if(empty($verify_data) || $verify_data['check_time'] == '0000-00-00 00:00:00'){
	        Log::error('phone number not verified . tel=' . $tel . ',check_time=' . $verify_data['check_time']);
	        EC::fail(EC_NOT_VFY);
	    }
	    if(strtotime($verify_data['expire_time']) < time()){
	        Log::error('code is time out verify . tel=' . $tel . ',expire_time=' . $verify_data['expire_time']);
	        EC::fail(EC_VFY_EPR);
	    }
	
	    //判断用户是否已存在(没注册过或者未被删除)
	    $user_model = $this->model('user');
	    $user_data = $user_model->getUserInfoByTel($tel,array(),true);
	    if(!empty($user_data) && $user_data['status'] == '1'){
	        Log::error('user exist:' . $tel);
	        EC::fail(EC_USR_EST);
	    }else if(!empty($user_data) && $user_data['enabled_status'] == '2'){
	        Log::error('user disable:' . $tel);
	        EC::fail(EC_USE_UNA);
	    }
	
	    $conf_arr = $this->getConfig('conf');
	    $pi_key = openssl_pkey_get_private($conf_arr['private_key']);
	    //解析密码
	    Log::notice('request param . pwd=' . $pwd);
	    $pwd = base64_decode($pwd);
	    $decrypted_pwd = '';
	    //解密密码
	    openssl_private_decrypt($pwd, $decrypted_pwd, $pi_key);
	    Log::notice('decrypt pwd=' . $decrypted_pwd);
	
	    $user_id = $this->model('id')->getUserId();
	    //密码md5加密
	    $md5_pwd = md5($user_id . $decrypted_pwd);
	    
	    //注册用户写入数据库
	    if(! $user_model->createUser(array(
                            	        'id'		=> $user_id,
                            	        'tel' 		=> $tel,
                            	        'name'		=> '',
                            	        'password'	=> $md5_pwd
                            	     ))
	       ){
	        //注册出错
	        Log::error('add user error!');
	        EC::fail(EC_USR_ADD);
	    }
	    EC::success(EC_OK);
	}
	
	/**
	 * 发送验证码
	 * @param unknown $req_data
	 */
	private function sendSmsCode($req_data){
	    $tel = $req_data['tel'];
	    if(!$tel){
	        Log::error("sendSmsCode tel is empyt . ");
	        EC::fail(EC_PAR_BAD);
	    }
	    
	    // 1-注册 2-找回密码
	    $type = $req_data['type'] ? $req_data['type'] : 1;
	    
	    $checkisreg = $this->model('user')->getUserInfoByTel($tel,array('id','account','status'),true);
	    if($type == '1'){
	        if(!empty($checkisreg) && $checkisreg['status'] == '1'){
	            Log::error('user exist . tel=' . $tel);
	            EC::fail(EC_USR_EST);
	        }else if(!empty($checkisreg) && $checkisreg['status'] == '2'){
	            Log::error('the user is disable!');
	            EC::fail(EC_USE_UNA);
	        }
	    }else if($type == '2'){
	        if(empty($checkisreg)){
	            Log::error(' user not exist . tel=' . $tel);
	            EC::fail(EC_USR_NON);
	        }
	    } else {
	        Log::error("sendSmsCode type is err . type=" . $type);
	        EC::fail(EC_PAR_BAD);
	    }
	    
	    //resend_after 判断是否可以重发   
	    $data = $this->model('verify')->getLastVer($tel,array('resend_after','check_time'));

	    if(!empty($data) && strtotime($data['resend_after'])>time() && !$data['check_time']) {
	    	Log::error('get code again error! tel=' . $tel . ',resend_after=' . $data['resend_after'] . ',check_time=' . $data['check_time']);
			EC::fail(EC_SND_AGA);
	    }

		$code = $this->createCode($tel);//生成验证码

		if(false === $code){
			Log::error('gen tel code error! tel=' . $tel);
			EC::fail(EC_GEN_CDE);
		}

		if(false === $this->sendCode($tel,$code,$type)){
			Log::error('send sms error! tel=' . $tel . ',code=' . $code);
			EC::fail(EC_SND_CDE);
		}

		$res = $this->addOrUpdVerifyCode($tel,$code);//验证码写入

		EC::success(EC_OK);
	}
	
	/**
	 * 发送验证码
	 * @param unknown $phonenum
	 * @param unknown $code
	 * @param unknown $type
	 */
	public function sendCode($phonenum,$code,$type){
	    if(!$phonenum || !$code){
	        Log::error('phonenum or code is empty . phonenum=' . $phonenum . ',code=' . $code);
	        return false;
	    }
	
	    $sms = $this->instance('sms');
	
	    //获取配置信息
	    $conf = $this->getConfig('conf');
	    /*配置参数*/
	    $root_url = $conf['sms_url'];
	    $MethodName = $conf['sms_MethodName'];
	    $Spid = $conf['sms_Spid'];
	    $Appid = $conf['sms_Appid'];
	    $password = $conf['sms_password'];
	    $Ims = $conf['sms_Ims'];
	    $Key = $conf['sms_Key'];
	    $timeout = $conf['sms_timeout'];
	    $word = $conf['sms_word'];
	    if($type == '1'){
	        $modelId = $conf['sms_modelId'];
	        return (true === $sms->send($root_url,$MethodName,$Spid,$Appid,$password,$Ims,$Key,$phonenum,$modelId,$code,$timeout,$word));
	    }else if($type == '2'){
	        $modelId = '10003';
	        return (true === $sms->send($root_url,$MethodName,$Spid,$Appid,$password,$Ims,$Key,$phonenum,$modelId,'找回密码功能',$code,$timeout));
	    }
	}
	
	/**
	 *验证码生成
	 */
	private function createCode(){
	    $verify_model = $this->model('verify');
	    $conf = $this->getConfig('conf');
	    $code_length = $conf['code_length'];
	    //生成验证码
	    $text_obj = $this->instance('text');
	    $code = $text_obj->randString($code_length,1);
	    while($verify_model->checkCodeExist($code)){
	        $code = $text_obj->randString($code_length,1);
	    }
	    Log::notice("createCode suc . code=" . $code);
	    return $code;
	}
	
	/**
	 * 写入验证码
	 * @param unknown $tel
	 * @param unknown $code
	 */
	private function addOrUpdVerifyCode($tel,$code){
	    $conf = $this->getConfig('conf');
	    $code_expire = $conf['code_expire'];
	    $code_resend_after = $conf['resend_after'];
	
	    $verify_model = $this->model('verify');
	    //写入验证码
	    $currTime = time();
	    if(false === $verify_model->replaceVerify($tel, $code, date('Y-m-d H:i:s',$currTime + $code_expire), date('Y-m-d H:i:s',$currTime + $code_resend_after)) ){
	        Log::error('addOrUpdVerifyCode err! tel=' . $tel . ',code=' . $code);
	        return false;
	    }
	    return true;
	}
	
}
