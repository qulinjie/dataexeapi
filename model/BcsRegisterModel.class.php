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
	
	public function getInfoBcsRegister($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->from()->select();
	    }else{
	        $data = $this->where($where)->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createBcsRegister($param = array()){
	    if(! $this->insert(array(
	        'id'                   => $param['id'],
	        'user_id'              => $param['user_id'],
	        'MCH_NO'               => $param['MCH_NO'],    // 商户编号
	        'SIT_NO'               => $param['SIT_NO'],                // 客户证件类型
	        'CUST_CERT_TYPE'       => $param['CUST_CERT_TYPE'],          // 客户证件类型
	        'CUST_CERT_NO'         => $param['CUST_CERT_NO'],            // 客户证件号码
	        'CUST_NAME'            => $param['CUST_NAME'],          // 客户名称
	        'CUST_ACCT_NAME'       => $param['CUST_ACCT_NAME'],      // 客户账户名
	        'CUST_SPE_ACCT_NO'     => $param['CUST_SPE_ACCT_NO'],     // 客户结算账户
	        'CUST_SPE_ACCT_BKTYPE' => $param['CUST_SPE_ACCT_BKTYPE'],    // 客户结算账户行别
	        'CUST_SPE_ACCT_BKID'   => $param['CUST_SPE_ACCT_BKID'],	// 客户结算账户行号
	        'CUST_SPE_ACCT_BKNAME' => $param['CUST_SPE_ACCT_BKNAME'],	// 客户结算账户行名
	        'ENABLE_ECDS'          => $param['ENABLE_ECDS'],        // 是否开通电票
	        'IS_PERSON'            => $param['IS_PERSON'],          // 是否个人
	        'CUST_PHONE_NUM'       => $param['CUST_PHONE_NUM'],      // 客户手机号码
	        'CUST_TELE_NUM'        => $param['CUST_TELE_NUM'],       // 客户电话号码
	        'CUST_ADDR'            => $param['CUST_ADDR'],       // 客户地址
	        'RMRK'                 => $param['RMRK'],           // 客户备注
	        'comment'              => $param['comment'],           // 备注
	        'status'	=>	3,
	        'is_delete'	=>	1,
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}

	public function checkIsExist($user_id){
		return true == $this->where(array('user_id=?'),array($user_id))->from(null,'id')->select();
	}


	public function getSitNo($user_id = -1){
		$data = $this->where('user_id=?',$user_id)->from(null,['SIT_NO'])->select();
		return $data ? $data[0]['SIT_NO'] : false;
	}
}