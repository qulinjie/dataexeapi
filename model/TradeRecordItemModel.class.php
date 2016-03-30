<?php
class TradeRecordItemModel extends Model {
    
	public function tableName(){
		return 'c_trade_record_item';
	}
	
	// 是否删除 1-正常 2-已删除
	public static $_is_delete_false = 1;
	public static $_is_delete_true = 2;
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	    
	    if(empty($params['is_delete']) ){
    	    $keys[] = 'is_delete = ?';
    	    $values[] = TradeRecordItemModel::$_is_delete_false;
	    }
	     
	    $fields = [ 'trade_record_id','ids'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            default:
	                $keys[] = "{$val}=?";
	                $values[] = $params[$val];
	                break;
	        }
	    }
	     
	    Log::notice('getSearchCnt ==== >>> keys=' . json_encode($keys) . ',values=' . json_encode($values) );
	    return $this->count(null, 'id', $keys, $values);
	}
	
	public function getSearchList($params = array(), $page = null, $count = null){
	    $model = $this->from();
	     
	    $where = [];
	    $fields = [ 'trade_record_id' ];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            default:
	                $where[] = "{$val}='{$params[$val]}'";
	                break;
	        }
	    }
	    
	    if(empty($params['is_delete']) ){
	       $where[] = 'is_delete=1'; // 1-正常 2-删除
	    }
	    
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $model->where( $where );
	     
	    if($params['trade_record_id_list'] && is_array($params['trade_record_id_list']) && !empty($params['trade_record_id_list'])){
	        $model->where( array('trade_record_id' => $params['trade_record_id_list']) );
	    }
	    
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateTradeRecordItem($param, $where){
	    Log::notice('update ==== >>> where=' . json_encode($where) . ',param=' . json_encode($param));
	    if(empty($where)){
	        Log::error('!!! upate all rows of record .');
	        return false;
	    }
	    if(empty($param)){
	        Log::error('!!! param is empyt .');
	        return false;
	    }
	    return $this->update($param, $where);
	}
	
	public function getInfoTradeRecordItem($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->where(array('is_delete'=>TradeRecordItemModel::$_is_delete_false))->from()->select();
	    }else{
	        $data = $this->where($where)->where(array('is_delete'=>TradeRecordItemModel::$_is_delete_false))->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createTradeRecordItem($param = array()){
	    Log::notice('create ==== >>> param=' . json_encode($param) );
	    if(! $this->insert(array(
    	        'id'	            =>	$param['id'],
                'trade_record_id'   =>	$param['trade_record_id'],
                'order_no'	=>	$param['order_no'],
                'itme_no'	=>	$param['itme_no'],
                'itme_seq'	=>	$param['itme_seq'],
                'item_name'	=>	$param['item_name'],
                'item_size'	=>	$param['item_size'],
                'item_type'	=>	$param['item_type'],
                'item_price'	=>	$param['item_price'],
                'item_count'	=>	$param['item_count'],
                'item_weight'	=>	$param['item_weight'],
                'item_delivery_addr'	=>	$param['item_delivery_addr'],
                'item_factory'	    =>	$param['item_factory'],
                'item_amount'	    =>	$param['item_amount'],
                'item_count_send'	=>	$param['item_count_send'],
                'item_weight_send'	=>	$param['item_weight_send'],
                'item_amount_send'	=>	$param['item_amount_send'],
	            'bid_price'	        =>	$param['bid_price'],
	            'bid_amount'	    =>	$param['bid_amount'],
                'comment'	        =>	$param['comment'],
	            'record_type'          =>	$param['record_type'],
                'is_delete'	        =>	TradeRecordItemModel::$_is_delete_false,
                'add_timestamp'	    =>	date('Y-m-d H:i:s',time()),
	    		'item_comp_name_buyer'	=>	$param['item_comp_name_buyer'],
	    		'item_comp_name_buyer_code'	=>	$param['item_comp_name_buyer_code']
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}