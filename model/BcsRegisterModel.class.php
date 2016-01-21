<?php
class BcsRegisterModel extends Model {
    
	public function tableName(){
		return 'c_bcs_register';
	}
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	    
	    $keys[] = 'is_delete = ?';
	    $values[] = 1; 
	    
	    $fields = [ 'status', 'SIT_NO', 'ACCOUNT_NO', 'time1', 'time2'];
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
	     
	    if($params['user_id_list'] && !empty($params['user_id_list']) ){
	        $user_id_str = '';
	        foreach ($params['user_id_list'] as $val){
	            $user_id_str =  $user_id_str . $val . ',';
	        }
	        $keys[] = 'user_id in ( ' . substr($user_id_str,0,-1) .' ) and \'1\'=? ';
	        $values[] = '1';
	    }
	    
	    Log::notice('getSearchCnt ==== >>> keys=' . json_encode($keys) . ',values=' . json_encode($values) );
	    return $this->count(null, 'id', $keys, $values);
	}
	
	public function getSearchList($params = array(), $page = null, $count = null){
	    $model = $this->from();
	     
	    $where = [];
	    $fields = [ 'status', 'SIT_NO', 'ACCOUNT_NO', 'time1', 'time2'];
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
	     
	    if($params['user_id_list'] && !empty($params['user_id_list']) ){
	        $model->where(array('user_id' => $params['user_id_list']));
	    }
	    
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateBcsRegister($param, $where){
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
	
	public function createBcsRegister($param = array()){
	    if(! $this->insert($param)){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}

	public function getList($params = array() , $fields = '*'){
	    $params && $this->where($params);
	    return $this->from(null,$fields)->select();
	}
}