<?php

class UserController extends Controller {
    
    public function handle($params = array(), $req_data = array()) {
        switch ($params[0]) {
            case "send_sms_code":
                $this->sendSmsCode($req_data);
                break;
            default:
				Log::error('method not found .');
				EC::fail(EC_MTD_NON);
				break;
		}	
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
	    
	    //TODO check register
	    
	    //TODO check resend
	    Log::notice("sendSmsCode str .  tel=" . $tel);
	    
	    $type = '1';
	    $phonenum = '13265431549';
	    $code = '验证码385812，您正在使用DDMG服务者入驻业务，需要进行验证，请勿向任何人提供您收到的短信验证码';
	    
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
	    $sendResult = false;
		if($type == '1'){			
			$modelId = $conf['sms_modelId'];
			$sendResult =  (true === $sms->send($root_url,$MethodName,$Spid,$Appid,$password,$Ims,$Key,$phonenum,$modelId,$code,$timeout,$word));
		}else if($type == '2'){
			$modelId = '10003';
			$sendResult =  (true === $sms->send($root_url,$MethodName,$Spid,$Appid,$password,$Ims,$Key,$phonenum,$modelId,'找回密码功能',$code,$timeout));
		}
		Log::notice("sendSmsCode end .  sendResult=" . $sendResult);
		return $sendResult;
	    
	}
	
}
