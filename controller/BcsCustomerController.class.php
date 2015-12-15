<?php
/**
 * 授权码 
 * @author zhangkui
 *
 */
class BcsCustomerController extends BaseController {

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
                default:
                    Log::error('method not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('bcsCustomer');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsCustomer');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $user_id = $req_data['user_id'];
        unset($req_data['user_id']);
    
        $bcsCustomer_model = $this->model('bcsCustomer');
        $res = $bcsCustomer_model->updateBcsCustomer($req_data,array('user_id' => $user_id));
        if(false === $res){
            Log::error('updateBcsCustomer faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsCustomer');
        $data = $code_model->getInfoBcsCustomer($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getBcsCustomerId();
        $req_data['id'] = $id;
        
        $bcsCustomer_model = $this->model('bcsCustomer');
        $bcsCustomer_model->startTrans(); // 事务开始

        /*
         * 修改授权码 ，已使用次数 +1
         */
        $code_model = $this->model('authorizationCode');
        $params = array();
        $params['used_count'] = ((int)$req_data['code_used_count']) + 1;
        $res = $code_model->updateAuthCode($params,array('id' => $req_data['code_id']));
        if(false === $res){
            Log::error('updateAuthCode faild ! rollback .');
            $bcsCustomer_model->rollback(); // 事务回滚
            EC::fail(EC_UPD_REC);
        }
        
        /*
         * 增加 代付款订单  
         */
        $data = $bcsCustomer_model->createBcsCustomer($req_data);
        if(false === $data){
            Log::error('createBcsCustomer Fail! rollback .');
            $bcsCustomer_model->rollback(); // 事务回滚
            EC::fail(EC_ADD_REC);
        }
        $bcsCustomer_model->commit(); // 事务提交
        
        EC::success(EC_OK,$id);
    }
    
}