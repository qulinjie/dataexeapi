<?php
/**
 * 授权码 
 * @author zhangkui
 *
 */
class TradeRecordController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
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
    
    public function create($req_data){
        $id = $this->model('id')->getTradeRecordId();
        $req_data['id'] = $id;
        $tradeRecord_model = $this->model('tradeRecord');
        $data = $tradeRecord_model->createTradeRecord($req_data);
        if(false === $data){
            Log::error('createTradeRecord Fail!');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,$id);
    }
    
}