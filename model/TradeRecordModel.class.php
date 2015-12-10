<?php
class TradeRecordModel extends Model {
    
	public function tableName(){
		return 'c_trade_record';
	}
	
	// 是否删除 1-正常 2-已删除
	public static $_is_delete_false = 1;
	public static $_is_delete_true = 2;
	
	// 订单交易状态 1-待付款 2-已付款 3-拒付
    public static $_status_waiting = 1;
    public static $_status_paid = 2;
    public static $_status_refuse = 3;
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	     
	    $keys[] = 'is_delete = ?';
	    $values[] = TradeRecordModel::$_is_delete_false;
	     
	    $fields = [ 'order_no', 'user_id', 'code', 'time1', 'time2','type','status', 
	        'order_time1', 'order_time2', 'seller_name', 'seller_conn_name', 'order_sum_amount1', 'order_sum_amount2'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            case 'time1':
	                $keys[] = "add_timestamp >= ?";
	                $values[] = $params[$val];
	                break;
	            case 'time2':
	                $keys[] = "add_timestamp <= ?";
	                $values[] = $params[$val];
	                break;
                case 'order_time1':
                    $keys[] = "order_timestamp >= ?";
                    $values[] = $params[$val];
                    break;
                case 'order_time2':
                    $keys[] = "order_timestamp <= ?";
                    $values[] = $params[$val];
                    break;
                case 'seller_name':
                    $keys[] = "{$val} like ?";
                    $values[] = '%' . $params[$val] . '%';
                    break;
                case 'order_sum_amount1':
                    $keys[] = "order_sum_amount >= ?";
                    $values[] = $params[$val];
                    break;
                case 'order_sum_amount2':
                    $keys[] = "order_sum_amount <= ?";
                    $values[] = $params[$val];
                    break;
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
	    $fields = [ 'order_no', 'user_id', 'code', 'time1', 'time2','type','status',
	        'order_time1', 'order_time2', 'seller_name', 'seller_conn_name', 'order_sum_amount1', 'order_sum_amount2'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            case 'time1':
	                $where[] = "add_timestamp >= '{$params[$val]}'";
	                break;
	            case 'time2':
	                $where[] = "add_timestamp <= '{$params[$val]}'";
	                break;
                case 'order_time1':
                    $where[] = "order_timestamp >= '{$params[$val]}'";
                    break;
                case 'order_time2':
                    $where[] = "order_timestamp <= '{$params[$val]}'";
                    break;
                case 'seller_name':
                    $where[] = "{$val} like '%{$params[$val]}%'";
                    break;
                case 'order_sum_amount1':
                    $where[] = "order_sum_amount >= '{$params[$val]}'";
                    break;
                case 'order_sum_amount2':
                    $where[] = "order_sum_amount <= '{$params[$val]}'";
                    break;
	            default:
	                $where[] = "{$val}='{$params[$val]}'";
	                break;
	        }
	    }
	     
	    $where[] = 'is_delete=1'; // 1-正常 2-删除
	     
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $model->where( $where );
	     
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateTradeRecord($param, $where){
	    Log::notice('update ==== >>> where=' . json_encode($where) );
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
	
	public function getInfoTradeRecord($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->where(array('is_delete'=>TradeRecordModel::$_is_delete_false))->from()->select();
	    }else{
	        $data = $this->where($where)->where(array('is_delete'=>TradeRecordModel::$_is_delete_false))->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createTradeRecord($param = array()){
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
	        'user_id' => $param['user_id'],
	        'code' => $param['code'],
	        'seller_id'	=>	$param['seller_id'],
	        'seller_name'	=>	$param['seller_name'],
	        'seller_conn_name'	=>	$param['seller_conn_name'],
	        'seller_tel'	=>	$param['seller_tel'],
	        'seller_comp_phone'	=>	$param['seller_comp_phone'],
	        'order_no'	=>	$param['order_no'],
	        'order_timestamp'  =>	$param['order_timestamp'],
	        'order_goods_name'	=>	$param['order_goods_name'],
	        'order_goods_size'	=>	$param['order_goods_size'],
	        'order_goods_type'	=>	$param['order_goods_type'],
	        'order_goods_price'	=>	$param['order_goods_price'],
	        'order_goods_count'	=>	$param['order_goods_count'],
	        'order_delivery_addr'	=>	$param['order_delivery_addr'],
	        'order_sum_amount'	=>	$param['order_sum_amount'],
	        'order_status'	=>	$param['order_status'],
	        'pay_timestamp'	=>	$param['pay_timestamp'],
	        'comment'	=>	$param['comment'],
	        'is_delete'	=>	TradeRecordModel::$_is_delete_false,
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}