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
                case 'create':  // 不需要 登录
                    $this->create($req_data);
                    break;
                case 'pay':
                    $this->pay($req_data);
                    break;
                case 'getNextId':
                    $this->getNextId($req_data);
                    break;
                    
                case 'create_add':
                    $this->create_add($req_data);
                    break;
                case 'auditOneTradRecord':
                    $this->auditOneTradRecord($req_data);
                    break;
                default:
                    Log::error('page not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('tradeRecord');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('tradeRecord');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['id'];
        $user_id = $req_data['user_id'];
        unset($req_data['id']);
        unset($req_data['user_id']);
    
        $tradeRecord_model = $this->model('tradeRecord');
        $res = $tradeRecord_model->updateTradeRecord($req_data,array('id' => $id,'user_id' => $user_id));
        if(false === $res){
            Log::error('updateTradeRecord faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('tradeRecord');
        $data = $code_model->getInfoTradeRecord($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id_model = $this->model('id');
        $tradeRecord_model = $this->model('tradeRecord');
        $tradeRecordItem_model = $this->model('tradeRecordItem');
        $tradeRecord_model->startTrans(); // 事务开始

        /*
         * 修改授权码 ，已使用次数 +1
         */
        /* $code_model = $this->model('authorizationCode');
        $params = array();
        $params['used_count'] = ((int)$req_data['code_used_count']) + 1;
        $res = $code_model->updateAuthCode($params,array('id' => $req_data['code_id']));
        if(false === $res){
            Log::error('updateAuthCode faild ! rollback .');
            $tradeRecord_model->rollback(); // 事务回滚
            EC::fail(EC_UPD_REC);
        } */
        
        /*
         * 增加 付款订单  
         */
        
        foreach ($req_data as $key => $val){
//             Log::error('----------------------------------trade_record val------------------------------params==>>' . var_export($val, true));
            Log::notice("createTrade-str .  key=" . $key);
            $id = $id_model->getTradeRecordId();
            $params = array();
            $params['id'] = $id;
            $params['user_id'] = $val['user_id'];
            $params['seller_name'] = $val['seller_name'];
            $params['seller_id'] = $val['seller_id'];
            $params['order_no'] = $val['order_no'];
            $params['partner_name'] = $val['partner_name'];
            $params['partner_tel'] = $val['partner_tel'];
            $params['partner_company_tel'] = $val['partner_company_tel'];
            $params['partner_company_name'] = $val['partner_company_name'];
            $params['order_amount'] = $val['order_amount'];
            $params['order_bid_amount'] = $val['order_bid_amount'];
            $params['order_new_amount'] = $val['order_new_amount'];
            $params['order_timestamp'] = $val['order_timestamp'];
            $params['order_status'] = $val['order_status'];
            $params['check_status'] = $val['check_status'];
            $params['send_status'] = $val['send_status'];
//             Log::error('----------------------------------trade_record------------------------------params==>>' . var_export($params, true));
            $data = $tradeRecord_model->createTradeRecord($params);
            if(false === $data){
                Log::error('createTradeRecord Fail! rollback . key=' . $key);
                $tradeRecord_model->rollback(); // 事务回滚
                EC::fail(EC_ADD_REC);
            }
            
            $seq = 0;
            foreach ($val['item'] as $item_key => $item_val){
//                 Log::error('----------------------------------trade_record_item------------------------------params==>>' . var_export($item_val, true));
                $params_item = array();
                $seq ++;
                $params_item['trade_record_id'] = $id;
                $params_item['id'] = $id_model->getTradeRecordItemId();
                $params_item['itme_seq'] = $seq;
                $params_item['order_no'] = $item_val['order_no'];
                $params_item['itme_no'] = $item_val['itme_no'];
                $params_item['item_name'] = $item_val['item_name'];
                $params_item['item_type'] = $item_val['item_type'];
                $params_item['item_size'] = $item_val['item_size'];
                $params_item['item_factory'] = $item_val['item_factory'];
                $params_item['item_count'] = $item_val['item_count'];
                $params_item['item_weight'] = $item_val['item_weight'];
                $params_item['item_price'] = $item_val['item_price'];
                $params_item['bid_price'] = $item_val['bid_price'];
                $params_item['item_delivery_addr'] = $item_val['item_delivery_addr'];
                $params_item['item_amount'] = $item_val['item_amount'];
                $params_item['bid_amount'] = $item_val['bid_amount'];
                
                $data = $tradeRecordItem_model->createTradeRecordItem($params_item);
                if(false === $data){
                    Log::error('createTradeRecordItem Fail! rollback . key=' . $key);
                    $tradeRecord_model->rollback(); // 事务回滚
                    EC::fail(EC_ADD_REC);
                }
            }
            Log::notice("createTrade-end .  key=" . $key . ',id=' . $id);
        }
        
        $tradeRecord_model->commit(); // 事务提交
        
        EC::success(EC_OK);
    }
    
    public function pay($req_data){
        $tradeRecord_model = $this->model('tradeRecord');
        $bcsTrade_model = $this->model('bcsTrade');
        
        $id = $req_data['id'];
        $user_id = $req_data['user_id'];
        
        Log::notice('tradeRecord-pay . id=' . $id . ',user_id=' . $user_id . ',req_data==>>' . var_export($req_data, true));
        
        /**
         * 查询 订单 
         */
        $data = $tradeRecord_model->getInfoTradeRecord(array('id' => $id, 'user_id' => $user_id));
        if(empty($data)) {
            Log::error('getInfo empty !');
            EC::fail(EC_RED_EMP);
        }
        
        /**
         * 增加 资金流水记录表
         */
        
        /*
         * 修改 代付款订单 状态
         */
        $params = array();
        $params['order_status'] = $req_data['order_status'];
        $params['pay_timestamp'] = $req_data['pay_timestamp'];
        $tradeRecord_model = $this->model('tradeRecord');
        $res = $tradeRecord_model->updateTradeRecord($params,array('id' => $id,'user_id' => $user_id));
        if(false === $res){
            Log::error('updateTradeRecord faild !');
            EC::fail(EC_UPD_REC);
        }
    
        EC::success(EC_OK);
    }
    
    public function getNextId(){
        $id_model = $this->model('id');
        $id = $id_model->getTradeRecordId();
        EC::success(EC_OK,$id); 
    }

    public function create_add($req_data){
        $id_model = $this->model('id');
        $tradeRecord_model = $this->model('tradeRecord');
        $tradeRecordItem_model = $this->model('tradeRecordItem');
        
        $tradeRecord_model->startTrans(); // 事务开始
        
        $id = $id_model->getTradeRecordId();
        
        $req_data['id'] = $id;
        $data = $tradeRecord_model->createTradeRecord($req_data);
        if(false === $data){
            Log::error('createTradeRecord Fail! rollback .' );
            $tradeRecord_model->rollback(); // 事务回滚
            EC::fail(EC_ADD_REC);
        }
        
        foreach ($req_data['item'] as $item_key => $item_val){
            $item_val['trade_record_id'] = $id;
            $item_val['id'] = $id_model->getTradeRecordItemId();
            $data = $tradeRecordItem_model->createTradeRecordItem($item_val);
            if(false === $data){
                Log::error('createTradeRecordItem Fail! rollback . key=' . $item_key);
                $tradeRecord_model->rollback(); // 事务回滚
                EC::fail(EC_ADD_REC);
            }
        }
        
        $tradeRecord_model->commit(); // 事务提交
        
        EC::success(EC_OK);
    }
    
    public function auditOneTradRecord($req_data){
    	$tradeRecord_model = $this->model('tradeRecord');    	
    	
    	$id = intval($req_data['id']);
    	$apply_status = intval($req_data['apply_status']); //审批状态  1待审批  2审批通过 3审批驳回	
    	Log::notice('tradeRecord-auditOneTradRecord . id=' . $id . ',apply_status=' . $apply_status . ',req_data==>>' . var_export($req_data, true));
    	
    	if(!in_array($apply_status, array(1,2,3))){
    		Log::error('apply_status is error!');
    		EC::fail(EC_PAR_ERR);
    	}
    	
    	$data = $tradeRecord_model->getInfoTradeRecord(array('id' => $id));
    	if(empty($data)) {
    		Log::error('getInfo empty !');
    		EC::fail(EC_RED_EMP);
    	}
    	
    	$params = array();
    	$params['apply_status'] = $apply_status;
    	if(2 == $apply_status){
    		$params['order_status'] = 1; //对审批通过的改订单状态为待付款
    	}
    	$params['apply_timestamp'] = $req_data['apply_timestamp'];   	
    	$res = $tradeRecord_model->updateTradeRecord($params, array('id' => $id));
        if(false === $res){
            Log::error('updateTradeRecord faild !');
            EC::fail(EC_UPD_REC);
        }
    
        EC::success(EC_OK);
    }
    
}