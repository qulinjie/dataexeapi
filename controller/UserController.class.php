<?php

class UserController extends Controller {

    public function handle($params = array(), $req_data = array()) {
        switch ($params[0]) {
            case "sendSmsCode":
                $this->sendSmsCode($req_data);
                break;
            case "register":
                $this->register($req_data);
                break;
            case "login":
                $this->login($req_data);
                break;
			case "isLogin":
				$this->isLogin($req_data);
				break;
			case "getLoginUser":
				$this->getLoginUser($req_data);
				break;
            case "loginOut":
                $this->loginOut();
                break;
			case 'updatePersonalAuthInfo': //更新个人认证信息
				$this->updatePersonalAuthInfo($req_data);
				break;
			case 'updateCompanyAuthInfo': //更新企业认证信息
				$this->updateCompanyAuthInfo($req_data);
				break;
			case 'loginPasswordReset': //登录密码重置
				$this->loginPasswordReset($req_data);
				break;
			case "getUserBasicInfo":
			    $this->getUserBasicInfo($req_data);
			    break;
			case "getUserInfo":
			    $this->getUserInfo($req_data);
			    break;
			case 'isAdmin':
				$this->isAdmin($req_data);
				break;
			case 'getCnt':
				$this->getCnt($req_data);
				break;
			case 'getList':
				$this->getList($req_data);
				break;
            default:
				Log::error('method not found .');
				EC::fail(EC_MTD_NON);
				break;
		}
	}

	private function login($req_data){
	    $user_model = $this->model('user');
	    $user_info = $user_model->getUserInfoByTel($req_data['tel'],array(),true);

	    if(empty($user_info) ) {
	        Log::error('login . user not exsit . ');//用户不存在
	        EC::fail(EC_LOGIN_PAR_REC);
	    } else if($user_info['status'] == '2') {
			Log::error('login . user disable! id=' . $user_info['id']);
			EC::fail(EC_USE_UNA);
		}
	    if(UserController::buildPassword($user_info['id'], $req_data['pwd']) != $user_info['password']){
	        Log::error('login . pwd error');//密码错误
	        EC::fail(EC_LOGIN_PAR_REC);
	    }

	    //设置已登录
	    $this->setLoginSession($user_info);

	    $session = Controller::instance('session');
	    unset( $user_info['password'] );
	    $session->set('loginUser', $user_info);

	    Log::notice('end login . sessionId=' . $session->get_id() );
	    // check
	    Log::notice('check setLoginSession . is_set[loginUser]=' . ($session->is_set('loginUser')) );
	    Log::notice('check setLoginSession . get[loginUser]=' . json_encode($session->get('loginUser')) );

	    EC::success(EC_OK,$user_info);
	}

	private function loginOut(){
	    try{
    	    $session = Controller::instance( 'session' );
    	    $user_id = $session->get('id');
    	    Log::notice("loginOut . user_id=" . $user_id);

    	    $session->clear();//清空session
    	    $session->destroy();//清空session
    	    $cookie = $this->instance('cookie');
    	    $cookie->clear(Router::getBaseUrl());//清空cookie
	    } catch (Exception $e) {
	        Log::error('loginOut . e=' . $e->getMessage());
	    }
	    EC::success(EC_OK);
	}

	private function setLoginSession($session_data = array()){
	    $session = Controller::instance( 'session' );
	    $session->clear();
	    $session->set('id', $session_data['id']);
	    $session->set('tel', $session_data['tel']);
	    $session->set('auth_id', $session_data['auth_id']);
	    $session->set('name', $session_data['name']);
	    unset( $session_data['password'] );
		unset( $session_data['pay_password'] );//支付密码一起删除
	    $session->set('loginUser', $session_data);
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

	private function register($req_data){
	    //检查验证码
	    $checkCmsCodeRes = self::checkCmsCode($req_data['tel'],$req_data['code']);
		$checkCmsCodeRes!= EC_OK && EC::fail($checkCmsCodeRes);

	    //判断用户是否已存在(没注册过或者未被删除)
	    $user_model = $this->model('user');
	    $user_data = $user_model->getUserInfoByTel($req_data['tel'],array(),true);
	    if(!empty($user_data) && $user_data['status'] == '1'){
	        Log::error('user exist:' . $req_data['tel']);
	        EC::fail(EC_USR_EST);
	    }else if(!empty($user_data) && $user_data['enabled_status'] == '2'){
	        Log::error('user disable:' . $req_data['tel']);
	        EC::fail(EC_USE_UNA);
	    }

	    $user_id = $this->model('id')->getUserId();
		$certification_id = $this->model('id')->getCertificationId();
	    //密码md5加密
	    if(!$md5_pwd = UserController::buildPassword($user_id, $req_data['pwd'])){
			Log::error('password decrypt error');
			EC::fail(EC_PWD_DEC);
		}

	    //注册用户写入数据库
		try{
			$user_model->startTrans();
			if(!$user_model->createUser(array('id'=>$user_id,'tel'=>$req_data['tel'],'name'=>'','password'=>$md5_pwd))
			   ||!$this->model('certification')->add($certification_id,$user_id)){
				throw new Exception(); //错误信息，底层sql有写文件
			}

			$user_model->commit();
		}catch (Exception $e){
			//注册出错
			$user_model->rollback();
			Log::error('add user error!');
			EC::fail(EC_USR_ADD);
		}

	    EC::success(EC_OK,array('register_certification_id' => $certification_id));
	}

	/**
	 * 发送验证码
	 * @param unknown $req_data
	 */
	private function sendSmsCode($req_data){
	    $tel = $req_data['tel'];
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

	/**
	 * 检查验证码
	 * @param $tel
	 * @param $code
	 * @return int
	 */
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

	/**
	 * @param $req_data['realName','filePath','fileName','id']
	 */
    private function updatePersonalAuthInfo($req_data) {
        if (!$req_data['realName'] || !$req_data['filePath'] || !$req_data['fileName'] || !$req_data['id']) {
            Log::error('updatePersonalAuth params is empty');
            EC::fail(EC_PAR_BAD);
        }

        if (!$certInfo = $this->model('certification')->get(array('id' => $req_data['id']))) {
            Log::error('updatePersonalAuth data not exists id=' . $req_data['id']);
            EC::fail(EC_DAT_NON);
        }

        try {
            $this->model('certification')->startTrans();
            if (!$this->model('certification')->updatePersonalAuth($req_data['realName'],$req_data['fileName'],$req_data['filePath'], $req_data['id'])
                || !$this->model('user')->updatePersonalAuth(2,$certInfo[0]['user_id'])) {
                throw new Exception('certificationModel method updatePersonalAuth is fail ');
            }
            $this->model('certification')->commit();
        } catch (Exception $e) {
            $this->model('certification')->rollback();
            Log::error('updatePersonalAuthInfo is fail msg (' . $e->getMessage() . ')');
            EC::fail(EC_OTH);
        }

        EC::success(EC_OK);
	}

    /**
     * @param $req_data['legalPerson','companyName','license','filePath','fileName','id']
     */
    private function updateCompanyAuthInfo($req_data)
    {
        if (!$req_data['legalPerson'] || !$req_data['companyName'] ||!$req_data['license'] || !$req_data['filePath'] || !$req_data['fileName'] || !$req_data['id']) {
            Log::error('updateCompanyAuthInfo params is empty');
            EC::fail(EC_PAR_BAD);
        }

        if (!$certInfo = $this->model('certification')->get(array('id' => $req_data['id']))) {
            Log::error('updateCompanyAuthInfo data not exists id=' . $req_data['id']);
            EC::fail(EC_DAT_NON);
        }

        try {
            $this->model('certification')->startTrans();
            if (!$this->model('certification')->updateCompanyAuth($req_data['legalPerson'],$req_data['companyName'],$req_data['license'],$req_data['fileName'],$req_data['filePath'], $req_data['id']) ||
                !$this->model('user')->updateCompanyAuth(2,$certInfo[0]['user_id'])) {
                throw new Exception('certificationModel method updateCompanyAuth is fail ');
            }
            $this->model('certification')->commit();
        } catch (Exception $e) {
            $this->model('certification')->rollback();
            Log::error('updateCompanyAuthInfo is fail msg (' . $e->getMessage() . ')');
            EC::fail(EC_OTH);
        }

        EC::success(EC_OK);
    }

	private function isLogin($req_data)
	{
		$session = self::instance('session');
		$data ['isLogin'] = $session->get('loginUser') ? 1 : 0;
		EC::success(EC_OK,$data);
	}

	private function getLoginUser($req_data)
	{
		$session = self::instance('session');
		if(!$data ['loginUser'] = $session->get('loginUser')){
			$data ['loginUser'] = [];
		}

		EC::success(EC_OK,$data);
	}

	private function loginPasswordReset($req_data)
	{
		$session = self::instance('session');
		if(!$loginUser = $session->get('loginUser')){
			Log::error('setPassword not Login');
			EC::fail(EC_NOT_LOGIN);
		}

		$basicInfo = $this->model('user')->getUserBasicInfo($loginUser['id']);
		if($basicInfo['password'] != self::buildPassword($basicInfo['id'], $req_data['oldPwd'])){
			Log::error('setPassword oldPassword error');
			EC::fail(EC_PWD_WRN);
		}

		$params = array('password' => self::buildPassword($basicInfo['id'],$req_data['newPwd']));


		if(!$this->model('user')->updateUser($params,array('id' => $basicInfo['id']))){
			Log::error('setPassword update password error msg('.$this->model('user')->getErrorInfo().')');
			EC::fail(EC_PWD_UPD);
		}

		MessageController::addMsg($loginUser['id'],200);
		EC::success(EC_OK);
	}

	private function getUserBasicInfo($req_data)
	{
	    $user_model = $this->model('user');
	    $user_info = $user_model->getUserBasicInfo($req_data['id'],array(),true);
	    EC::success(EC_OK,$user_info);
	}

	public function getUserInfo($req_data){
	    $code_model = $this->model('user');
	    $data = $code_model->getUserInfo($req_data, array());
	    EC::success(EC_OK,$data);
	}

	private function isAdmin($req_data)
	{
		$session = self::instance('session');
		if(!$loginUser = $session->get('loginUser')){
			Log::error('setPassword not Login');
			EC::fail(EC_NOT_LOGIN);
		}

		EC::success(EC_OK,['status' => $loginUser['account'] == 'admin']);
	}

	private function getCnt($req_data)
	{
		EC::success(EC_OK,$this->model('user')->getCnt());
	}

	private function getList($req_data)
	{

	}
}
