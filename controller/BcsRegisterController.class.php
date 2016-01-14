<?php
/**
 * @author zhangkui
 *
 */
class BcsRegisterController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
                case 'searchCnt':
                    $this->getSearchCnt($req_data);
                    break;
                case 'searchList':
                    $this->getSearchList($req_data);
                    break;
                case 'update':
                    $this->update($req_data);
                    break;
                case 'getInfo':
                    $this->getInfo($req_data);
                    break;
                case 'create':
                    $this->create($req_data);
                    break;
                case 'getSitNo':
                    $this->getSitNo($req_data);
                    break;
                default:
                    Log::error('method not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $param = array();
        isset($req_data['ACCOUNT_NO']) && $param['ACCOUNT_NO'] = $req_data['ACCOUNT_NO'];
        isset($req_data['ACT_TIME']) && $param['ACT_TIME'] = date('Y-m-d H:i:s',strtotime($req_data['ACT_TIME']));
        $where = array('SIT_NO' => $req_data['SIT_NO']);
        if(!$this->model('bcsRegister')->updateBcsRegister($param,$where)){
            Log::error('updateBcsRegister fail !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getInfoBcsRegister($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $session = self::instance('session');
        if(!$loginUser = $session->get('loginUser')){
            Log::error('create not login');
            EC::fail(EC_NOT_LOGIN);
        }

//         if($this->model('bcsRegister')->checkIsExist($loginUser['id'],$req_data['CUST_SPE_ACCT_NO'])){
//             Log::error('bank card already exists');
//             EC::fail(EC_REC_EST);
//         }

        $req_data['id']         = $this->model('id')->getBcsRegisterId();
        $req_data['user_id']    = $loginUser['id'];
        $req_data['ACCOUNT_NO'] = '';

        if(!$this->model('bcsRegister')->createBcsRegister($req_data)){
            Log::error('createBcsRegister Fail! rollback .');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK);
    }

    private function getSitNo($req_data)
    {
        $session = $this->instance('session');
        if(!$loginUser= $session->get('loginUser')) {
            Log::bcsError('not Login');
            EC::fail(EC_NOT_LOGIN);
        }

        $data = $this->model('bcsRegister')->getSitNo($loginUser['id']);
        $data ? EC::success(EC_OK,['SIT_NO' => $data ]) : EC::fail(EC_SIT_NO_NON);
    }
}