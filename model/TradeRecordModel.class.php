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