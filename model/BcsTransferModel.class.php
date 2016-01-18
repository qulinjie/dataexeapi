<?php
class BcsTransferModel extends Model {
    
	public function tableName(){
		return 'c_bcs_transfer';
	}
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	     
	    $keys[] = 'is_delete = ?';
	    $values[] = 1;
	    
	    $fields = [ 'user_id', 'SIT_NO', 'time1', 'time2', 'FMS_TRANS_NO', 'status' ];
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
	    $fields = [ 'user_id', 'SIT_NO', 'time1', 'time2', 'FMS_TRANS_NO', 'status' ];
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
	            default:
	                $where[] = "{$val}='{$params[$val]}'";
	                break;
	        }
	    }
	     
	    $where[] = "is_delete = 1";
	    
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $model->where( $where );
	     
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateBcsTransfer($param, $where){
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
	
	public function getInfoBcsTransfer($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->from()->select();
	    }else{
	        $data = $this->where($where)->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createBcsTransfer($param = array()){
	    Log::notice('create ==== >>> param=' . json_encode($param) );
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
	        'user_id' => $param['user_id'],
	        'transfer_type' => $param['transfer_type'],
	        'MCH_NO' => $param['MCH_NO'],
	        'SIT_NO' => $param['SIT_NO'],
	        'MCH_TRANS_NO' => $param['MCH_TRANS_NO'],
	        'FMS_TRANS_NO'	=>	$param['FMS_TRANS_NO'],
	        'CURR_COD'	=>	$param['CURR_COD'],
	        'TRANS_AMT'	=>	$param['TRANS_AMT'],
	        'TRANS_FEE'	=>	$param['TRANS_FEE'],
	        'TOTALAMT'	=>	$param['TOTALAMT'],
	        'TRANS_STS'	=>	$param['TRANS_STS'],
	        'TRANS_TIME'  =>	$param['TRANS_TIME'],
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