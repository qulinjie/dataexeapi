<?php
class VerifyModel extends Model{
    
	public function tableName(){
		return 'c_verify';
	}
	
	public function addVerify($tel, $code, $expire_time, $resend_after, $check_time = 0){
		return $this->insert(array(
                    				'tel' => $tel, 
                    		        'code' => $code, 
                    				'expire_time' => $expire_time, 
                    				'resend_after' => $resend_after,
                    				'check_time' => $check_time,
                    		        'add_timestamp' => date('Y-m-d H:i:s',time())
		                          ));
	}
	
	public function updateVerify($tel, $code, $expire_time, $resend_after, $check_time = 0){
		return $this->update(array('code' => $code, 
				'expire_time' => $expire_time, 
				'resend_after' => $resend_after,
				'check_time' => $check_time), array('tel' => $tel));
	}
	/*
	*手机验证码表的更新或添加
	*/
	public function replaceVerify($tel, $code, $expire_time, $resend_after, $check_time = 0){
		$cnt = $this->where('tel=?', $tel)->count();
		if($cnt){
			return $this->updateVerify($tel, $code, $expire_time, $resend_after, $check_time);
		}else{
			return $this->addVerify($tel, $code, $expire_time, $resend_after,$check_time);
		}
	}
	
	public function setCheckTime($tel, $check_time){
		$cnt = $this->where('tel=?', $tel)->count();
		if(! $cnt) {
			Log::error('verify record not exist!');
			Log::error('error:' . $this->getErrorInfo());
			return false;
		}
		else return $this->update(array('check_time' => $check_time), array('tel'=>$tel));
	}

	public function getLastVer($tel,$fields = array()){
		if(empty($fields)){
			$data = $this->where('tel=?', $tel)->order('add_timestamp desc')->limit(0,1)->from()->select();
		}else{
			$data = $this->where('tel=?', $tel)->order('add_timestamp desc')->limit(0,1)->from(null, $fields)->select();
		}
		if(!count($data)) {
			Log::error('verify record not exist!');
			Log::error('error:' . $this->getErrorInfo());
			return array();
		}
		return $data[0];
	}

	/*
	*查询重发时间是否过时（是否可以重发）
	*/
	/*public function select($tel, $check_time){
		$cnt = $this->where('tel=?',$tel)->count();
		if(! $cnt){
			Log::error('verify record not exist!');
			Log::error('error:' . $this->getErrorInfo());
			return false;
		}
		else return $this->update(array('check_time' => $check_time), array('tel'=>$tel));
	}*/
	
	public function checkCodeExist($code){
		return (0 < $this->where(array('code' => $code))->count());
	}
	
	public function getVerifyRecordByCode($code){
		$ret = $this->getAll(array('code' => $code));
		if(empty($ret)){
			return array();
		}else{
			return $ret[0];
		}
	}
	
	public function getVerifyRecordByTel($tel,$code){
		$ret = $this->getAll(array('tel' => $tel,'code' => $code));
		if(empty($ret)){
			return array();
		}else{
			return $ret[0];
		}
	}
	
}