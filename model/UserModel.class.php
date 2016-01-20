<?php

class UserModel extends Model {
	public function tableName(){
		return 'c_user';
	}	
	
	// 是否删除 1-正常 2-已删除
	public static $_is_delete_false = 1;
	public static $_is_delete_true = 2;
	
	public function createUser($param = array()){
		if(! $this->insert(array(
                				'id'	       => $param['id'],
                				'account'      => $param['tel'], 
                				'password'     => $param['password'],
                		        'nicename'	   => '',
                		        'pay_password' => '',
		                        'company_name' => '',
                				'status'	   => 1,
		                        'is_delete'    => 1,
		                        'comment'      => '',
                				'personal_authentication_status' =>	1,
								'company_authentication_status'	 =>	1,
                				'add_timestamp' => date('Y-m-d H:i:s',time())
                		  ))
		    ){
			Log::error('create user error: ' . $this->getErrorNo() . ' : ' . $this->getErrorInfo());
			return false;
		}
		return true;
	}
	
	public function updateUser($param, $where){
		if(!$where || !$param){
			Log::error('!!! updateUser upate all rows or SET is empty');
			return false;
		}
		
		return $this->update($param, $where);
	}

	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	     
	    $keys[] = 'is_delete = ?';
	    $values[] = 1;
	     
	    $fields = [ 'status', 'nicename', 'account', 'time1', 'time2'];
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
                case 'nicename':
                    $keys[] = "{$val} like ?";
                    $values[] = '%' . $params[$val] . '%';
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
	    $fields = [ 'status', 'nicename', 'account', 'time1', 'time2'];
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
                case 'nicename':
                    $where[] = "nicename like '%{$params[$val]}%'";
                    break;
	            default:
	                $where[] = "{$val}='{$params[$val]}'";
	                break;
	        }
	    }
	     
	    $where[] = "is_delete = 1";
	     
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $model->where( $where );
	
	    if($params['user_id_list'] && is_array($params['user_id_list']) && !empty($params['user_id_list'])){
	        $model->where( array('id' => $params['user_id_list']) );
	    }
	    
	    if($page && $count){
	        $model->pageLimit($page, $count);
	    }
	    return $model->order('add_timestamp desc')->select();
	}
	
	public function getList($params = array() , $fields = '*'){
	    $params && $this->where($params);
	    return $this->from(null,$fields)->select();
	}

	public function getInfoUser($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->where(array('is_delete'=>UserModel::$_is_delete_false))->from()->select();
	    }else{
	        $data = $this->where($where)->where(array('is_delete'=>UserModel::$_is_delete_false))->from(null,$fields)->select();
	    }
	    return $data;
	}
	
}
