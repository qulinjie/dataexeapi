<?php
class BcsMarketModel extends Model {
    
	public function tableName(){
		return 'c_bcs_market';
	}
	
	public function updateBcsMarket($param, $where){
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
	
	public function getInfoBcsMarket($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->from()->select();
	    }else{
	        $data = $this->where($where)->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createBcsMarket($param = array()){
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
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}