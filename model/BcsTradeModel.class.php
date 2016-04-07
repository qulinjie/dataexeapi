<?php
class BcsTradeModel extends Model {
    
	public function tableName(){
		return 'c_bcs_trade';
	}
	
	// 交易状态 1-成功 2-失败 3-未知
	public static $_status_success = 1;
	public static $_status_failed = 2;
	public static $_status_unknown = 3;
	
	public static function getSearchkeysValues($params){
		$keys = array();
		$values = array();
		
		$keys[] = 'is_delete = ?';
		$values[] = 1;
		 
		$fields = [ 'b_user_id', 'seller_name', 'time1', 'time2', 'order_no', 'status','MCH_TRANS_NO',
		'FMS_TRANS_NO', 'seller_name', 'amount1', 'amount2', 'order_id', 'ACCOUNT_NO', 'debitCreditFlag', 'oppositeAcctName' ];
		foreach ($fields as $key => $val){
			if( 0 == strlen(strval($params[$val])) && !$params[$val] ){
				continue;
			}
			switch ( $val ) {
				case 'time1':
					$keys[] = "TRANS_TIME >= ?";
					$values[] = $params[$val];
					break;
				case 'time2':
					$keys[] = "TRANS_TIME <= ?";
					$values[] = $params[$val];
					break;
				case 'seller_name':
					$keys[] = "{$val} like ?";
					$values[] = '%' . $params[$val] . '%';
					break;
				case 'oppositeAcctName':
					$keys[] = "{$val} like ?";
					$values[] = '%' . $params[$val] . '%';
					break;
				case 'amount1':
					$keys[] = "TX_AMT >= ?";
					$values[] = $params[$val];
					break;
				case 'amount2':
					$keys[] = "TX_AMT <= ?";
					$values[] = $params[$val];
					break;
				case 'status':
					if('31' == $params[$val]){
						$keys[] = 'status in ( ' . self::$_status_unknown . ',' . self::$_status_success . ' ) and \'1\'=? ';
						$values[] = 1;
						break;
					}
				default:
					$keys[] = "{$val}=?";
					$values[] = $params[$val];
					break;
					
				/* if($params['s_user_id_list'] && !empty($params['s_user_id_list']) ){
				 $s_user_id_str = '';
				foreach ($params['s_user_id_list'] as $val){
				$s_user_id_str =  $s_user_id_str . $val . ',';
				}
				$keys[] = 's_user_id in ( ' . substr($s_user_id_str,0,-1) .' ) and \'1\'=? ';
				$values[] = '1';
				}
				if($params['b_user_id_list'] && !empty($params['b_user_id_list']) ){
				$b_user_id_str = '';
				foreach ($params['b_user_id_list'] as $val){
				$b_user_id_str =  $b_user_id_str . $val . ',';
				}
				$keys[] = 'b_user_id in ( ' . substr($b_user_id_str,0,-1) .' ) and \'1\'=? ';
				$values[] = '1';
				} */
					
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
	    
	    Log::notice('getSearchList ==== >>> $params=' . json_encode($params) );
	    
	    $keys = array();
	    $values = array();
	    
	    $kv_arr = self::getSearchkeysValues($params);
	    if(is_array($kv_arr) && isset($kv_arr['keys'])){
	    	$keys = $kv_arr['keys'];
	    }
	    if(is_array($kv_arr) && isset($kv_arr['values'])){
	    	$values = $kv_arr['values'];
	    }
	    
	    Log::notice('getSearchList ==== >>> keys=' . json_encode($keys) . ',values=' . json_encode($values) );
	    $model->where( $keys , $values);
	    
	    /* if($params['s_user_id_list'] && !empty($params['s_user_id_list']) ){
	        $model->where(array('s_user_id' => $params['s_user_id_list']));
	    }
	    if($params['b_user_id_list'] && !empty($params['b_user_id_list']) ){
	        $model->where(array('b_user_id' => $params['b_user_id_list']));
	    } */
	    
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
// 	    return $model->order('add_timestamp desc')->select();
	    return $model->order('TRANS_TIME desc')->select();
	}
	
	public function updateBcsTrade($param, $where){
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
	
	public function getInfoBcsTrade($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->from()->select();
	    }else{
	        $data = $this->where($where)->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createBcsTrade($param = array()){
	    Log::notice('create ==== >>> param=' . json_encode($param) );
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
	        'order_id' => $param['order_id'],
	        'order_no' => $param['order_no'],
	        'b_user_id' => $param['b_user_id'],
	        's_user_id' => $param['s_user_id'],
	        'seller_name' => $param['seller_name'],
	        'MCH_NO'	=>	$param['MCH_NO'],
	        'CTRT_NO'	=>	$param['CTRT_NO'],
	        'BUYER_SIT_NO'	=>	$param['BUYER_SIT_NO'],
	        'SELLER_SIT_NO'	=>	$param['SELLER_SIT_NO'],
	        'FUNC_CODE'	=>	$param['FUNC_CODE'],
	        'TX_AMT'	=>	$param['TX_AMT'],
	        'SVC_AMT'  =>	$param['SVC_AMT'],
	        'BVC_AMT'	=>	$param['BVC_AMT'],
	        'CURR_COD'	=>	$param['CURR_COD'],
	        'MCH_TRANS_NO'	=>	$param['MCH_TRANS_NO'],
	        'ORGNO'	=>	$param['ORGNO'],
	        'TRANS_TIME'	=>	$param['TRANS_TIME'],
	        'comment'	=>	$param['comment'],
	        'status'	=>	$param['status'],
	        'shareDate'	=>	$param['shareDate'],
	        'debitCreditFlag'	=>	$param['debitCreditFlag'],
	        'accountBalance'	=>	$param['accountBalance'],
	        'record_bank_type'	=>	$param['record_bank_type'],
	        'ACCOUNT_NO'	=>	$param['ACCOUNT_NO'],
	        'oppositeAcctNo'	=>	$param['oppositeAcctNo'],
	        'oppositeAcctName'	=>	$param['oppositeAcctName'],
	        'payeeBankNo'	=>	$param['payeeBankNo'],
	        'payeeBankName'	=>	$param['payeeBankName'],
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}