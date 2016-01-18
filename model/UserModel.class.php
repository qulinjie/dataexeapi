<?php

class UserModel extends Model {
	public function tableName(){
		return 'c_user';
	}
	
	public function filterUserIdList($idList = array(), $keepOrder = false){
		$data = $this->where(array('id' => $idList))->where(array('status'=>'1'))->from(null, 'id')->select();
		if(empty($data)) return array();
		$ret_arr = array();
		foreach ($data as $item){
			$ret_arr [] = $item['id'];
		}

		if($keepOrder){
			//需要保持顺序
			$ret_arr = array_intersect($idList, $ret_arr);
		}
	
		return $ret_arr;
	}

	public function filterTelIdList($idList = array(), $fields = array(),$status = false){
		if(!$status){
			$this->where(array('status'=>'1'));
		}

		if(!empty($fields)){
			$data = $this->where(array('id' => $idList))->from(null,$fields)->select();
		}else{
			$data = $this->where(array('id' => $idList))->from()->select();
		}

		if(empty($data)) return array();
		
		return $data;
	}	
	
	public function getAllUserIdList(){
		$data = $this->where(array('status'=>'1'))->from(null, 'id')->select();
		if(empty($data)){
			return array();
		}
		$ret_data = array();
		foreach ($data as $item){
			$ret_data [] = $item['id'];
		}
		return $ret_data;
	}
	
	public function getUserBasicInfo($userId, $fields = array(),$status = false) {
		if(!$status){
			$this->where(array('status'=>'1'));
		}
		if(empty($fields))
			$data = $this->where('id=?', $userId)->from()->select();
		else $data = $this->where('id=?', $userId)->from(null, $fields)->select();
		if(empty($data)){
			Log::error('user id not find ' . $userId);
			return array();
		}
		return $data[0];
	}

	public function getUserByCode($invite_code, $fields = array()) {
		if(empty($fields))
			$data = $this->where('invite_code=?', $invite_code)->where(array('status'=>'1'))->from()->select();
		else $data = $this->where('invite_code=?', $invite_code)->where(array('status'=>'1'))->from(null, $fields)->select();
		if(empty($data)){
			Log::error('user invite_code not find ' . $invite_code);
			return array();
		}
		return $data[0];
	}

	public function checkExistence($userId){
		$data = $this->where(array('status'=>'1'))->getUserBasicInfo($userId);
		return (!empty($data));
	}
	
	public function getUserInfoByTel( $tel,$fields=[],$status = false){
		if(!$status){
			$this->where(array('status'=>'1'));
		}else{
			$this->where(array('status  != 3'));
		}
		if(empty($fields))
			$data = $this->where('account=?', $tel)->from()->select();
		else $data = $this->where('account=?', $tel)->from(null,$fields)->select();
		if(empty($data)){
			Log::notice('tel not find ' . $tel);
			return array();
		}
		return $data[0];
	}

	//用于注册判断是否可以再次进行注册
	public function getInfoByTel( $tel, $fields=[] ){
		if(empty($fields))
			$data = $this->where('tel=?', $tel)->from()->select();
		else $data = $this->where('tel=?', $tel)->from(null,$fields)->select();
		if(empty($data)){
			Log::notice('tel not find ' . $tel);
			return array();
		}
		return $data[0];
	}

	public function getUserInfoByCity( $city, $fields=[] ){
		if(empty($fields))
			$data = $this->where('city=?', $city)->where(array('status'=>'1'))->from()->select();
		else $data = $this->where('city=?', $city)->where(array('status'=>'1'))->from(null,$fields)->select();
		if(empty($data)){
			Log::notice('city not find ' . $city);
			return array();
		}
		return $data[0];
	}
	
	public function getUserInfo($where = array(),$fields = array()){
		if(empty($fields)){
			$data = $this->where($where)->where(array('status'=>'1'))->from()->select();
		}else{
			$data = $this->where($where)->where(array('status'=>'1'))->from(null,$fields)->select();	
		}
		return $data;
	}
	
	public function createUser($param = array()){
		if(! $this->insert(array(
                				'id'	=>	$param['id'],
                				'account' => $param['tel'], 
                				'password' => $param['password'],
                		        'nicename'	=>	$param['name'],
                		        'pay_password' => '',
                				'status'	=>	1,
                				'personal_authentication_status'	=>	1,
								'company_authentication_status'	    =>	1,
                				'add_timestamp' => date('Y-m-d H:i:s',time())
                		  ))
		    ){
			Log::error('create user error: ' . $this->getErrorNo() . ' : ' . $this->getErrorInfo());
			return false;
		}
		return true;
	}
	
	public function updateUser($param, $where){
		if(empty($where)){
			Log::error('!!! upate all rows of user');
			return false;
		}
		if(empty($param)){
			return false;
		}
		return $this->update($param, $where);
	}
	
	
	
	/**
	 * feature_bit
	 * 0: home page
	 * 1: search page
	 */
	const F_BIT_HOME	=	0;
	const F_BIT_SEARCH	=	1;
	
	public function getFeatureBit($user_id, $i){
		$data = $this->getUserBasicInfo($user_id);
		if(empty($data)){
			Log::error('get feature, user not exist, user id:' . $user_id);
			return false;
		}
		$featrue_bit = $data['feature_bit'];
		if(! GF::is_int_or_intstr($featrue_bit)){
			Log::error('feature bit is not right, user id:' . $user_id);
			return false;
		}
		if($featrue_bit & (1 << $i)){
			return 1;
		}
		return 0;
	}
	
	public function setFeatureBit($user_id, $i, $v){
		$data = $this->getUserBasicInfo($user_id);
		if(empty($data)){
			Log::error('get feature, user not exist, user id:' . $user_id);
			return false;
		}
		$featrue_bit = $data['feature_bit'];
		if(! GF::is_int_or_intstr($featrue_bit)){
			Log::error('feature bit is not right, user id:' . $user_id);
			return false;
		}
		if((($featrue_bit >> $i) & 1) != $v){
			if($v == 1){
				$featrue_bit = $featrue_bit | (1 << $i);
			}else {
				$featrue_bit = $featrue_bit &  (~ (1 << $i));
			}
			if(false === $this->updateUser(array('feature_bit' => $featrue_bit), array('id' => $user_id))){
				Log::error('update feature bit error!');
				return false;
			}
		}else {
			Log::warning('feature bit already set, user id: ' . $user_id . ' number:' . $i);
		}
		return true;
	}

	/**
	 * @param $status
	 * @param $id
	 * @return bool
	 */
	public function updatePersonalAuth($status,$id){
		return $this->update(array('personal_authentication_status' => $status),array('id' => $id));
	}

	/**
	 * @param $status
	 * @param $id
	 * @return bool
	 */
	public function updateCompanyAuth($status,$id){
		return $this->update(array('company_authentication_status' => $status),array('id' => $id));
	}

	public function checkPayPassword($id){
		return true == $this->where(array('id=?','pay_password!=""'),$id)->from(null,array('1'))->select();
	}

	public function updatePayPassword($id,$payPassword){
		return $this->update(array('pay_password' => $payPassword),array('id'=>$id));
	}

	public function getPayPassword($id){
		$pwd = $this->where('id=?',$id)->from(null,array('pay_password'))->select();
		return $pwd ? $pwd[0]['pay_password'] : '';
	}

	public function getList($params = array(),$page = 1,$count = 100,$field = array('id',''))
	{

	}

	public function getCnt($params = null)
	{
		return $this->count(null,'id',$params);
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
	
}
