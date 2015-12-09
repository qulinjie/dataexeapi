<?php
class AuthorizationCodeModel extends Model {
    
	public function tableName(){
		return 'c_authorization_code';
	}
	
	// 是否删除 1-正常 2-已删除
	public static $_is_delete_false = 1;
	public static $_is_delete_true = 2;
	
	// 状态 1-正常/启用 2-停用 3-失效
	public static $_status_enabled = 1;
	public static $_status_disabled = 2;
	public static $_status_overdue = 3;
	
	// 使用方式 1-按次数 2-按时间段
	public static $_type_count = 1;
	public static $_type_time = 2;
	
	
	public function getSearchCnt($params = array()){
	    
	    $keys = array();
	    $values = array();
	    
	    $keys[] = 'is_delete = ?';
	    $values[] = AuthorizationCodeModel::$_is_delete_false;
	    
	    $fields = ['user_id', 'code', 'time1', 'time2','type','status'];
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
	    $fields = ['user_id', 'code', 'time1', 'time2','type','status'];
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
	    
	    $where[] = 'is_delete=1'; // 1-正常 2-删除
	    
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $model->where( $where );
	    
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function updateAuthCode($param, $where){
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
	
	public function getInfoAuthCode($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->where(array('is_delete'=>AuthorizationCodeModel::$_is_delete_false))->from()->select();
	    }else{
	        $data = $this->where($where)->where(array('is_delete'=>AuthorizationCodeModel::$_is_delete_false))->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createAuthCode($param = array()){
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
            'user_id' => $param['user_id'],
	        'code' => $param['code'],
	        'type'	=>	$param['type'],
	        'active_count'	=>	$param['active_count'],
	        'time_start'	=>	$param['time_start'],
	        'time_end'	=>	$param['time_end'],
	        'comment'	=>	$param['comment'],
            'used_count'	=>	0,
            'status'	=>	AuthorizationCodeModel::$_status_enabled,
	        'is_delete'	=>	AuthorizationCodeModel::$_is_delete_false,
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}