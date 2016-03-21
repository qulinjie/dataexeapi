<?php
class AdminController extends Controller {

    public function handle($params = array(), $req_data = array()) {
        if(empty($params)){
            EC::fail (EC_MTD_NON);
        }else {
            switch ($params[0]){
                case 'login':
                    $this->login($req_data);
                    break;
                case 'loginOut':
                    $this->loginOut($req_data);
                    break;
                case 'isLogin':
                    AdminController::isLogin($req_data);
                    break;
                case 'changePwd':
                    $this->changePwd($req_data);
                    break;
                case 'erp_login':
                    $this->erp_login($req_data);
                    break;
                default :
                    Log::error ('page not found . ' . $params[0]);
                    EC::fail (EC_MTD_NON);
                    break;
            }
        }
    }
    
    public static function isLogin($req_data)
    {
        $session = Controller::instance('session');
        if($session->is_set('loginUser')){
            EC::success(EC_OK,$session->get('loginUser'));
        }
        Log::error('isLogin . session[loginUser] is not set . id=' . $session->get_id() );
        EC::fail(EC_OTH);
    }
    
    public static function checkLogin(){
        if(!self::isLogin()){
            EC::fail(EC_NOT_LOGIN);
        }
    }
    
    protected function loginOut(){
        $session = Controller::instance('session');
        $session->delete('loginUser');
        $session->clear();
        EC::success(EC_OK);
    }
    
    private function login($req_data){
        $login_account	=	$req_data['account'];
        $login_password	=	$req_data['password'];
        $pincode	=	$req_data['pincode'];
        $login_csrf	=	$req_data['login_csrf'];
        $other_csrf	=	$req_data['other_csrf'];
        
        Log::notice('req_data-params==>>' . var_export($req_data, true));
        
        $admin_model = $this->model('admin');
        $loginUser = $admin_model->getAdminInfo(array('account' => $login_account));
        if(empty($loginUser)){
            Log::error('adminInfo not exist. login_account='.$login_account);
            EC::fail(EC_LOGIN_PAR_REC);
        }
        
        $decrypted_pwd = '';
        $privateKey  = openssl_pkey_get_private(self::getConfig('conf')['private_key']);
        $password = base64_decode($login_password);
        openssl_private_decrypt($password, $decrypted_pwd, $privateKey);
        if(!$decrypted_pwd){
            Log::error('login password is empty');
            EC::fail(EC_PWD_EMP);
        }
        
       if( password_verify($password, $loginUser['password']) ){
            Log::error('adminInfo password error. login_account=' . $login_account);
            EC::fail(EC_LOGIN_PAR_REC);
        }
        
        $loginUser['is_admin'] = 'yes';
        
        $this->setLoginSession($loginUser);
        Log::notice('login success . login_account=' . $login_account);
        EC::success(EC_OK,$loginUser);
    }
    
    protected function setLoginSession($loginUser){
        $session = Controller::instance('session');
        Log::notice('str setLoginSession . sessionId=' . $session->get_id() );
        unset( $loginUser['password'] );
        
        $session->set('id', $loginUser['id']);
        $session->set('auth_id', $loginUser['auth_id']);
        
        $session->set('loginUser', $loginUser);
        Log::notice('end setLoginSession . sessionId=' . $session->get_id() );
        // check
        Log::notice('check setLoginSession . is_set[loginUser]=' . ($session->is_set('loginUser')) );
        Log::notice('check setLoginSession . get[loginUser]=' . json_encode($session->get('loginUser')) );
    }
    
    public function changePwd($req_data){
        $id = $req_data['id'];
        $new_pwd = $req_data['new_pwd'];
        $old_pwd = $req_data['old_pwd'];
         
        $admin_model = $this->model('admin');
        $admin_info = $admin_model->getAdminInfo(array('id'=>$id));
         
        if(empty($admin_info)){
            Log::error('admin not exsit .');//用户不存在
            EC::fail(EC_PAR_ERR);
        }
        if(UserController::buildPassword($admin_info['id'], $old_pwd) != $admin_info['password']){
            Log::error('changePwd pwd error');//密码错误
            EC::fail(EC_PWD_WRN);
        }
         
        $params = array();
        $params['password'] = UserController::buildPassword($id, $new_pwd);
        $res = $admin_model->updateAdmin($params,array('id' => $id));
        if(false === $res){
            Log::error('changePwd faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    
    public function erp_login($req_data){
        $login_account = $req_data['data']['loginid'];
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
         
        $session = Controller::instance('session');
        $session->set('loginUser', $user_info);
        
        if( '1' != $user_info['is_paymanage'] ){
            Log::error('login failed . is not admin role . login_account=' . $login_account);
            
            $session = Controller::instance('session');
            $session->delete('loginUser');
            $session->clear();
            
            EC::fail(EC_LOGIN_PAR_REC);
        }
        $user_info['user_id'] = $user_info['usercode'];
        $user_info['account'] = $user_info['usercode'];
        $user_info['name'] = $user_info['username'];
        $user_info['is_admin'] = 'yes';
        
        $this->setLoginSession($user_info);
        Log::notice('login success . login_account=' . $user_info['user_id']);
        EC::success(EC_OK,$user_info);
    }
    
}