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
    
    public static function getSearchkeysValues($params){
    	$keys = array();
    	$values = array();
    	
    	if(empty($params['is_delete']) ){
    		$keys[] = 'is_delete = ?';
    		$values[] = self::$_is_delete_false;
    	}
    	
    	$fields = [ 
    	'order_no', 'user_id', 'audit_user_id_first', 'audit_user_id_second', 'code', 'time1', 'time2', 
    	'type', 'order_status', 'apply_status', 'is_delete', 'seller_id', 'backhost_status', 'order_time1', 
    	'order_time2', 'seller_name', 'seller_conn_name', 'order_sum_amount1', 'order_sum_amount2', 'ACCOUNT_NO',
    	'amount1', 'amount2','amount_type','erp_fgsmc' ];
    	
    	foreach ($fields as $key => $val){
    		if( !$params[$val] ){
    			continue;
    		}
    		switch ( $val ) {
    			/* case 'user_id':
    			 $keys[] = "(user_id=? or audit_user_id_first=? or audit_user_id_second =?)";
    			$values[] = $params[$val];
    			$values[] = $params[$val];
    			$values[] = $params[$val];
    			break; */
    			case 'backhost_status':
    				if(00 == backhost_status){
    					$keys[] = "{$val} = ?";
    					$values[] = 0;
    				}else{    					
    					$keys[] = "{$val} = ?";
    					$values[] = $params[$val];
    				}
    				break;
    			case 'order_no':
    				$keys[] = "{$val} like ?";
    				$values[] = '%' . $params[$val] . '%';
    				break;
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
    			case 'amount1':
    				$keys[] = "order_bid_amount >= ?";
    				$values[] = $params[$val];
    				break;
    			case 'amount2':
    				$keys[] = "order_bid_amount <= ?";
    				$values[] = $params[$val];
    				break;
    			case 'amount_type':
    				$keys[]   = "amount_type LIKE ?";
    				$values[] = '%'.$params[$val].'%';
    				break;
    			case 'erp_fgsmc':
    				$keys[] = "erp_fgsmc LIKE ?";
    				$values[] = '%'.$params[$val].'%';
    				break;
    			/* case 'order_status':
    				if('9' == $params[$val]){
    					$keys[] = 'order_status in ( ' . self::$_status_paid . ',' . self::$_status_refuse . ' ) and \'1\'=? ';
    					$values[] = '1';
    					break;
    				} */
    			default:
    				$keys[] = "{$val}=?";
    				$values[] = $params[$val];
    				break;
    		}
    	}
    	
    	return array('keys' => $keys, 'values' => $values);
    }
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	    
	    $kv_arr = self::getSearchkeysValues($params);
	    if(is_array($kv_arr) && isset($kv_arr['keys'])){
	    	$keys = $kv_arr['keys'];
	    }
	    if(is_array($kv_arr) && isset($kv_arr['values'])){
	    	$values = $kv_arr['values'];
	    }

	    Log::notice('getSearchCnt ==== >>> keys=' . json_encode($keys) . ',values=' . json_encode($values) );
	    return $this->count(null, 'id', $keys, $values);
	}
	
	public function getSearchList($params = array(), $page = null, $count = null){
	    $model = $this->from();
	     
		$kv_arr = self::getSearchkeysValues($params);
	    if(is_array($kv_arr) && isset($kv_arr['keys'])){
	    	$keys = $kv_arr['keys'];
	    }
	    if(is_array($kv_arr) && isset($kv_arr['values'])){
	    	$values = $kv_arr['values'];
	    }

	    Log::notice('getSearchList ==== >>> keys=' . json_encode($keys) . ',values=' . json_encode($values) );
	    $model->where( $keys , $values);
	     
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateTradeRecord($param, $where){
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
	    Log::notice('create ==== >>> param=' . json_encode($param) );	    
	    
	    $returnInsertId = true;
	    if(isset($param['id'])) $returnInsertId = false;
	    $param['is_delete'] = self::$_is_delete_false;
	    if(!isset($param['add_timestamp'])) { $param['add_timestamp'] =  date('Y-m-d H:i:s',time());}
	    
	    if(true && !$insertId = $this->insert($param, $returnInsertId)){
	    	Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	    	return false;
	    }
	    if(isset($param['id'])) $insertId = $param['id'];
	    return $insertId;
	    /* if(! $this->insert(array(
	        'id'           =>	$param['id'],
	        'user_id'      =>	$param['user_id'],	    	
	    	'audit_user_id_first'    =>	$param['audit_user_id_first'],
	    	'audit_user_id_second'   =>	$param['audit_user_id_second'],
	        'ACCOUNT_NO'   =>	$param['ACCOUNT_NO'],
	        'code'         =>	$param['code'],
	        'seller_id'    =>	$param['seller_id'],
	        'seller_name'   =>	$param['seller_name'],
	    	'seller_name_code'   =>	$param['seller_name_code'],
	        'comp_account'   =>	$param['comp_account'],
	        'bank_name'   =>	$param['bank_name'],
	        'amount_type'   =>	$param['amount_type'],
	        'useTodo'   =>	$param['useTodo'],
	        'partner_name'   =>	$param['partner_name'],
	        'partner_tel'   =>	$param['partner_tel'],
	        'partner_company_tel'   =>	$param['partner_company_tel'],
	        'partner_company_name'   =>	$param['partner_company_name'],
	        'apply_no'         =>	$param['apply_no'],
	        'order_no'         =>	$param['order_no'],
	        'order_amount'     =>	$param['order_amount'],
	        'order_timestamp'   =>	$param['order_timestamp'],
	        'order_status'     =>	$param['order_status'],
	        'pay_timestamp'    =>	$param['pay_timestamp'],
	        'check_status'     =>	$param['check_status'],
	        'check_timestamp'   =>	$param['check_timestamp'],
	        'send_status'      =>	$param['send_status'],
	        'send_timestamp'   =>	$param['send_timestamp'],
	        'order_timestamp'	=>	$param['order_timestamp'],
	        'order_status'	=>	$param['order_status'],
	        'order_bid_amount'	=>	$param['order_bid_amount'],
	        'order_new_amount'	=>	$param['order_new_amount'],
	        'comment'          =>	$param['comment'],
	        'record_type'          =>	$param['record_type'],
	        'is_delete'         =>	TradeRecordModel::$_is_delete_false,
	        'add_timestamp'   =>	date('Y-m-d H:i:s',time()),	    		
	    	'bank_no'       => $param['bank_no'],
	    	'bank_flag'       => $param['bank_flag'],
	    	'local_flag'       => $param['local_flag'],
	    	'erp_fgsdm'       => $param['erp_fgsdm'],
	    	'erp_bmdm'        => $param['erp_bmdm'],
	    	'erp_fgsmc'       => $param['erp_fgsmc'],
	    	'erp_bmmc'        => $param['erp_bmmc'],
	    	'erp_username'    => $param['erp_username']
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true; */
	}
	
}