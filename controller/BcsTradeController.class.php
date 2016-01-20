<?php
/**
 * @author zhangkui
 *
 */
class BcsTradeController extends BaseController {

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
        $code_model = $this->model('bcsTrade');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsTrade');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['id'];
        unset($req_data['id']);
        
        $bcsTrade_model = $this->model('bcsTrade');
        $res = $bcsTrade_model->updateBcsTransfer($req_data,array('id' => $id));
        if(false === $res){
            Log::error('updateBcsTransfer faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsTrade');
        $data = $code_model->getInfoBcsTrade($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getBcsTradeId();
        
        $params = $req_data['bcs_trade'];
        $params['id'] = $id;
        $params['order_id'] = $req_data['order_id'];
        $params['order_no'] = $req_data['order_no'];
        $params['b_user_id'] = $req_data['b_user_id'];
        $params['s_user_id'] = $req_data['s_user_id'];
        $params['seller_name'] = $req_data['seller_name'];
        $params['comment'] = $req_data['comment'];
        $params['status'] = $req_data['status'];
        
        $bcsTrade_model = $this->model('bcsTrade');
        $data = $bcsTrade_model->createBcsTrade($params);
        if(false === $data){
            Log::error('createBcsTrade Fail! rollback .');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,$id);
    }
    
}