<?php
class SpdInternetBankModel extends Model {
    
	public function tableName(){
		return 'c_spd_internet_bank';
	}
	
	// 是否删除 1-正常 2-已删除
	public static $_is_delete_false = 1;
	public static $_is_delete_true = 2;
	
	public function getSearchCnt($params = array()){
	    $keys = array();
	    $values = array();
	    
	    $keys[] = 'is_delete = ?';
	    $values[] = SpdInternetBankModel::$_is_delete_false;
	    
	    $fields = ['bankName', 'bankNo'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
                case 'bankName':
                    $keys[] = "{$val} like ?";
                    $values[] = '%' . $params[$val] . '%';
                    break;
	            case 'bankNo':
	                $keys[] = "bankNo = ?";
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
	    $fields = ['bankName', 'bankNo'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            case 'bankName':
	                $where[] = "{$val} like '%{$params[$val]}%'";
	                break;
                case 'bankNo':
                    $where[] = "bankNo = '{$params[$val]}'";
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
	
	public function updateSpdInterBank($param, $where){
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
	
	public function getInfoSpdInterBank($where = array(),$fields = array()){
	    Log::notice('getInfo ==== >>> where=' . json_encode($where) );
	    if(empty($fields)){
	        $data = $this->where($where)->where(array('is_delete'=>SpdInternetBankModel::$_is_delete_false))->from()->select();
	    }else{
	        $data = $this->where($where)->where(array('is_delete'=>SpdInternetBankModel::$_is_delete_false))->from(null,$fields)->select();
	    }
	    return $data;
	}
	
	public function createSpdInterBank($param = array()){
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
            'serialNo' => $param['serialNo'],
	        'bankName' => $param['bankName'],
	        'bankNo'	=>	$param['bankNo'],
	        'is_delete'	=>	SpdInternetBankModel::$_is_delete_false,
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
	public function deleteSpdInterBank($params = array()){
	    if(!$params){
	        Log::error('deleteCert delete all');
	        return false;
	    }
	
	    return $this->delete($params);
	}
}