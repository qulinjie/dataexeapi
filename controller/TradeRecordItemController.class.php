<?php
/**
 * 授权码 
 * @author zhangkui
 *
 */
class TradeRecordItemController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
//                 case 'searchCnt':
//                     $this->getSearchCnt($req_data);
//                     break;
                case 'searchList':
                    $this->getSearchList($req_data);
                    break;
                case 'update':
                     $this->update($req_data);
                     break;
//                 case 'getInfo':
//                     $this->getInfo($req_data);
//                     break;
                default:
                    Log::error('page not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('tradeRecordItem');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('tradeRecordItem');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['id'];
        unset($req_data['id']);       
    
        $tradeRecordItem_model = $this->model('tradeRecordItem');
        $res = $tradeRecordItem_model->updateTradeRecordItem($req_data,array('id' => $id));
        if(false === $res){
            Log::error('updateTradeRecordItem faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('tradeRecordItem');
        $data = $code_model->getInfoTradeRecordItem($req_data, array());
        EC::success(EC_OK,$data);
    }
    
}