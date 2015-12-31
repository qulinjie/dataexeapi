<?php


class FindPasswordController extends Controller
{

    public function handle($params = array(), $req_data = array())
    {
        switch ($params[0]) {
            case 'check':
                $this->check($req_data);
                break;
            case 'identity':
                $this->identity($req_data);
                break;
            case 'loginPasswordReset':
                $this->loginPasswordReset($req_data);
                break;
            default:
                Log::error('method not found ' . $params[0]);
                EC::fail(EC_MTD_NON);
                break;
        }
    }

    private function check($req_data)
    {
        $userInfo = $this->model('user')->getUserInfoByTel($req_data['tel'], array(), true);
        if (empty($userInfo)) {
            Log::error('findPassword check user not exist . ');//用户不存在
            EC::fail(EC_USR_NON);
        } else if ($userInfo['status'] == '2') { //用户被禁用
            Log::error('findPassword  check user disable! id=' . $userInfo['id']);
            EC::fail(EC_USE_UNA);
        } else {
            EC::success(EC_OK);
        }
    }

    private function identity($req_data)
    {
        $checkCmsCodeRes = UserController::checkCmsCode($req_data['tel'], $req_data['code']);
        $checkCmsCodeRes != EC_OK && EC::fail($checkCmsCodeRes);

        $userInfo = $this->model('user')->getUserInfoByTel($req_data['tel'], array());
        if (!$userInfo) {
            Log::error('findPassword identity user not exist . ');//用户不存在
            EC::fail(EC_USR_NON);
        }

        $where = array('user_id' => $userInfo['id'], 'real_name' => $req_data['real_name']);
        if (!$this->model('certification')->get($where)) {
            Log::error('findPassword identity certification not exist . ');//证书不存在
            EC::fail(EC_CERT_ERR);
        }

        EC::success(EC_OK);
    }

    /* ['tel','pwd']*/
    private function loginPasswordReset($req_data)
    {
        $userInfo = $this->model('user')->getUserInfoByTel($req_data['tel'], array());
        if (!$userInfo) {
            Log::error('findPassword loginPasswordReset user not exist:' . $req_data['tel']);
            EC::fail(EC_USR_EST);
        }

        //密码md5加密
        if (!$md5_pwd = UserController::buildPassword($userInfo['id'], $req_data['pwd'])) {
            Log::error('findPassword loginPasswordReset decrypt error');
            EC::fail(EC_PWD_DEC);
        }

        if(!$this->model('user')->updateUser(['password' => $md5_pwd],['id' => $userInfo['id']])){
            Log::error('findPassword loginPasswordReset update error');
            EC::fail(EC_PWD_UPD);
        }

        EC::success(EC_OK);
    }
}
