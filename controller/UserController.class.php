<?php

class UserController extends Controller {
	
	public static $userSessionKey = '_loginUser';

    public function handle($params = array(), $req_data = array()) {
        switch ($params[0]) {
            case "login":
                $this->login($req_data);
                break;
            case "getLoginUser":
                $this->getLoginUser($req_data);
                break;
            case "loginOut":
                $this->loginOut();
                break;
            case 'searchCnt':
                $this->getSearchCnt($req_data);
                break;
            case 'searchList':
                $this->getSearchList($req_data);
                break;
            case "sendSmsCode":
                $this->sendSmsCode($req_data);
                break;
            case "create":
                $this->create($req_data);
                break;            	
			case 'getList':
				$this->getList($req_data);
				break;
			case 'update':
			    $this->update($req_data);
			    break;	
			case 'delete':
			    $this->delete($req_data);
			    break;
		    case 'getInfo':
		        $this->getInfo($req_data);
		        break;
		        
	        case 'erp_login':
	            $this->erp_login($req_data);
	            break;
            default:
				Log::error('method not found .');
				EC::fail(EC_MTD_NON);
				break;
		}
	}
	
	/**
	 * 获取用户列表
	 * 备注：查询指定列 ，请传 fields = [...] 
	 */
	public function getList($req_data)
	{
	    //默认全部字段
	    $fields = '*';    
	    if(isset($req_data['fields'])){
	        $fields = $req_data['fields'];
	        unset($req_data['fields']);
	    }
	   
	    $data = $this->model('user')->getList($req_data,$fields);
	    EC::success(EC_OK,$data);
	}
	
	public function update($req_data)
	{	    
	    $id = $req_data['id'];
	    unset($req_data['id']);
	    
	    if(!$this->model('user')->updateUser($req_data,['id' => $id])){
	        Log::error('User update error');
	        EC::fail(EC_UPD_REC);
	    }
	    
	    EC::success(EC_OK);  	    
	}
	
	public function getSearchCnt($req_data){
	    $code_model = $this->model('user');
	    $data = $code_model->getSearchCnt($req_data);
	    EC::success(EC_OK,$data);
	}
	
	public function getSearchList($req_data){
	    $current_page = $req_data['current_page'];
	    $page_count = $req_data['page_count'];
	    unset($req_data['current_page']);
	    unset($req_data['page_count']);
	    $params = $req_data;
	
	    $code_model = $this->model('user');
	    $data = $code_model->getSearchList($params, $current_page, $page_count);	
	    EC::success(EC_OK,$data);
	}
	
	private function login($req_data){
	    $user_model = $this->model('user');
	    $user_info  = $user_model->getList(array('account' => $req_data['tel'] ),array('id','account','password','pay_password','nicename','company_name','status','is_delete','user_type'));

	    if(!$user_info || $user_info[0]['is_delete'] == '2') {
	        Log::error('login . user not exsit . ');//用户不存在
	        EC::fail(EC_LOGIN_PAR_REC);
	    } else if($user_info[0]['status'] == '2') {
			Log::error('login . user disable! id=' . $user_info[0]['id']);
			EC::fail(EC_USE_UNA);
		}
		
	    if(UserController::buildPassword($user_info[0]['id'], $req_data['pwd']) != $user_info[0]['password']){
	        Log::error('login . pwd error');//密码错误
	        EC::fail(EC_LOGIN_PAR_REC);
	    }

	    //设置已登录
	    //$this->setLoginSession($user_info[0]);
	    
	    $bcsRegister = $this->model('bcsRegister')->getList(array('user_id' => $user_info[0]['id']),array('SIT_NO'));
	    $user_info[0]['SIT_NO'] = $bcsRegister ? $bcsRegister[0]['SIT_NO'] : '';

	    $session = Controller::instance('session');
	    $session->set(self::$userSessionKey, $user_info[0]);

	    Log::notice('end login . sessionId=' . $session->get_id() );
	    // check
	    Log::notice('check setLoginSession . is_set[loginUser]=' . ($session->is_set(self::$userSessionKey)) );
	    Log::notice('check setLoginSession . get[loginUser]=' . json_encode($session->get(self::$userSessionKey)) );

	    EC::success(EC_OK,$user_info[0]);
	}

	private function loginOut(){
	    try{
    	    $session = Controller::instance( 'session' );    

    	    $session->clear();//清空session
    	    $session->destroy();//清空session
    	    $cookie = $this->instance('cookie');
    	    $cookie->clear(Router::getBaseUrl());//清空cookie
	    } catch (Exception $e) {
	        Log::error('loginOut . e=' . $e->getMessage());
	    }
	    EC::success(EC_OK);
	}

	public static function buildPassword($id, $pwd){
// 	    return "123456";// TODO for test
	    $conf_arr = Controller::getConfig('conf');
	    $pi_key = openssl_pkey_get_private($conf_arr['private_key']);
	    //解析密码
	    $pwd = base64_decode($pwd);
	    $decrypted_pwd = '';
	    //解密密码
	    openssl_private_decrypt($pwd, $decrypted_pwd, $pi_key);
	    Log::notice('--------------decrypted_pwd---buildPassword-----params==>>' . var_export($decrypted_pwd, true) . ' ,id=' . $id . ' ,MD5_pwd=' . md5($id . $decrypted_pwd));
	    return $decrypted_pwd ? md5($id . $decrypted_pwd) : false; //为false  表示解密失败
	}

	private function create($req_data){
	    $req_data['id'] = $this->model('id')->getUserId();
	    $req_data['password'] = md5($req_data['id'].$req_data['password']);
	    if(!$this->model('user')->createUser($req_data)){
	        Log::error('User create error');
	        EC::fail(EC_ADD_REC);
	    }
		
	    EC::success(EC_OK,array('id' => $req_data['id']));
	}


	private function sendSmsCode($req_data){
	    $tel = $req_data['tel'];
	    // 1-注册 2-找回密码
	    $type = $req_data['type'] ? $req_data['type'] : 1;

	    $checkisreg = $this->model('user')->getList(array('account' => $tel),array('id','account','status'));
	    if($type == '1'){
	        if($checkisreg){
	            Log::error('user exist . tel=' . $tel);
	            EC::fail(EC_USR_EST);
	        }
	    }else if($type == '2'){
	        if(!$checkisreg || $checkisreg[0]['is_delete'] == 2){
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

	
	public function sendCode($phonenum,$code,$type){
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

	public static function checkCmsCode($tel,$code){
		$verify_model = self::model('verify');
		$verify_data  = $verify_model->getVerifyRecordByTel($tel,$code);

		//是否验证通过
		if(empty($verify_data) ){//暂时注释|| $verify_data['check_time'] == '0000-00-00 00:00:00'){
			Log::error('phone number not verified . tel=' . $tel . ',check_time=' . $verify_data['check_time']);
			return EC_NOT_VFY;
		}
		if(strtotime($verify_data['expire_time']) < time()){
			Log::error('code is time out verify . tel=' . $tel . ',expire_time=' . $verify_data['expire_time']);
			return EC_VFY_EPR;
		}
		return EC_OK;
	}

	private function getLoginUser($req_data)
	{
	    Log::notice("getLoginUser . req_data = ##" . json_encode($req_data) . "##");
		$session = self::instance('session');
		$loginUser = empty($session->get('loginUser')) ? $session->get(self::$userSessionKey) : $session->get('loginUser');
		if(!$loginUser){
		    Log::error('User getLoginUser not login');
			EC::fail(EC_NOT_LOGIN);
		}
		Log::notice("getLoginUser . loginUser = ##" . json_encode($loginUser) . "##");
		EC::success(EC_OK,$loginUser);
	}
	
	public function getInfo($req_data){
	    $code_model = $this->model('user');
	    $data = $code_model->getInfoUser($req_data, array());
	    EC::success(EC_OK,$data);
	}
	
	public function erp_login($req_data){
	    $pwd = $req_data['data']['userpwd'];
	    
	    $conf_arr = Controller::getConfig('conf');
	    $pi_key = openssl_pkey_get_private($conf_arr['private_key']);
	    //解析密码
	    $pwd = base64_decode($pwd);
	    $decrypted_pwd = '';
	    //解密密码
	    openssl_private_decrypt($pwd, $decrypted_pwd, $pi_key);
	    
	    Log::notice('--------erp_login------decrypted_pwd---buildPassword-----params==>>' . var_export($decrypted_pwd, true) );
	    
	    $req_data['data']['userpwd'] = $decrypted_pwd;
	    
	    $code_model = $this->model('curlUser');
	    
	    $data = $code_model->erp_login($req_data);
	    
	    $user_info = $data['data'];
	    
        if( '1' != $user_info['is_partner'] ){
	        Log::error('login failed . is not is_partner role . login_account=' . $user_info['usercode']);
	        
	        $session = Controller::instance( 'session' );    
    	    $session->clear();//清空session
    	    $session->destroy();//清空session
    	    $cookie = $this->instance('cookie');
    	    $cookie->clear(Router::getBaseUrl());//清空cookie
    	    
	        EC::fail(EC_LOGIN_PAR_REC);
	    }
	    
	    $user_info['id'] = $user_info['usercode'];
	    $user_info['user_id'] = $user_info['usercode'];
	    $user_info['account'] = $user_info['usercode'];
	    $user_info['name'] = $user_info['username'];
	    
	    $session = Controller::instance('session');
	    $session->set(self::$userSessionKey, $user_info);
	    
	    Log::notice('end login . sessionId=' . $session->get_id() );
	    // check
	    Log::notice('check setLoginSession . is_set[loginUser]=' . ($session->is_set(self::$userSessionKey)) );
	    Log::notice('check setLoginSession . get[loginUser]=' . json_encode($session->get(self::$userSessionKey)) );
	    
	    EC::success(EC_OK,$user_info);
	}
	
}
