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
    
}