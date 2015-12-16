<?php
/**
 * @author zhangkui
 *
 */
class BcsMarketController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
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
    
    public function update($req_data){
        $id = $req_data['id'];
        unset($req_data['id']);
    
        $bcsMarket_model = $this->model('bcsMarket');
        $res = $bcsMarket_model->updateBcsMarket($req_data,array('id' => $id));
        if(false === $res){
            Log::error('updateBcsMarket faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsMarket');
        $data = $code_model->getInfoBcsMarket($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getBcsMarketId();
        $req_data['id'] = $id;
        
        $bcsMarket_model = $this->model('bcsMarket');
        $bcsMarket_model->startTrans(); // 事务开始

        /*
         * 修改授权码 ，已使用次数 +1
         */
        $code_model = $this->model('authorizationCode');
        $params = array();
        $params['used_count'] = ((int)$req_data['code_used_count']) + 1;
        $res = $code_model->updateAuthCode($params,array('id' => $req_data['code_id']));
        if(false === $res){
            Log::error('updateAuthCode faild ! rollback .');
            $bcsMarket_model->rollback(); // 事务回滚
            EC::fail(EC_UPD_REC);
        }
        
        /*
         * 增加 代付款订单  
         */
        $data = $bcsMarket_model->createBcsMarket($req_data);
        if(false === $data){
            Log::error('createBcsMarket Fail! rollback .');
            $bcsMarket_model->rollback(); // 事务回滚
            EC::fail(EC_ADD_REC);
        }
        $bcsMarket_model->commit(); // 事务提交
        
        EC::success(EC_OK,$id);
    }
    
}