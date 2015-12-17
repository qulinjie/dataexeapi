<?php
class BcsTradeModel extends Model {
    
	public function tableName(){
		return 'c_bcs_trade';
	}
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	     
	    $keys[] = 'is_delete = ?';
	    $values[] = BcsTradeModel::$_is_delete_false;
	     
	    $fields = [ 'order_no', 'user_id', 'code', 'time1', 'time2','type','order_status', 
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
	    $fields = [ 'order_no', 'user_id', 'code', 'time1', 'time2','type','order_status',
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
	        'b_user_id' => $param['b_user_id'],
	        's_user_id' => $param['s_user_id'],
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
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}