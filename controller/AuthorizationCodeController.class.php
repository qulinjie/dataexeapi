<?php
/**
 * 授权码 
 * @author zhangkui
 *
 */
class AuthorizationCodeController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('AuthorizationCodeController . params is empty . ');
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
                default:
                    Log::error('page not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('authorizationCode');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('authorizationCode');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['id'];
        $user_id = $req_data['user_id'];
        unset($req_data['id']);
        unset($req_data['user_id']);
        
        $code_model = $this->model('authorizationCode');
        $res = $code_model->updateAuthCode($req_data,array('id' => $id,'user_id' => $user_id));
        if(false === $res){
            Log::error('updateAuthCode faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('authorizationCode');
        $data = $code_model->getInfoAuthCode($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getAuthorizationCodeId();
        $req_data['id'] = $id;
        $code_model = $this->model('authorizationCode');
        $data = $code_model->createAuthCode($req_data);
        if(false === $data){
            Log::error('createAuthCode Fail!');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,$id);
    }
    
}