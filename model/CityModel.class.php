<?php
class CityModel extends Model {
    
	public function tableName(){
		return 'c_city';
	}
	
	public function getInfo($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->from()->select();
	    }else{
	        $data = $this->where($where)->from(null,$fields)->select();
	    }
	    return $data;
	}	
		
	public function getList($params = array() , $fields = '*'){
	    $params && $this->where($params);
	    return $this->from(null,$fields)->select();
	}
}