<?php
class BcsChildAccountModel extends Model {
    
	public function tableName(){
		return 'c_bcs_child_account';
	}
	
	public function deleteBcsChildAccount($where = array()){
	    Log::notice('delete ==== >>> where=' . json_encode($where) );
	    if(empty($where)){
	        return false;
	    }
	    return $this->delete($where);
	}
	
	public function createBcsChildAccount($param = array()){
	    Log::notice('create ==== >>> param=' . json_encode($param) );
	    if(! $this->insert(array(
	        'id'   =>	$param['id'],
	        'MCH_NO' => $param['MCH_NO'],
	        'MCH_SPE_ACCT_NAME' => $param['MCH_SPE_ACCT_NAME'],
	        'MCH_SPE_ACCT_NO' => $param['MCH_SPE_ACCT_NO'],
	        'MCH_BANK_NAME' => $param['MCH_BANK_NAME'],
	        'MCH_BANK_NO' => $param['MCH_BANK_NO'],
	        'ACCOUNT_NO' => $param['ACCOUNT_NO'],
	        'MCH_NAME' => $param['MCH_NAME'],
	        'MCH_ACCT_BAL' => $param['MCH_ACCT_BAL'],
	        'TYPE' => $param['TYPE'],
	        'comment'	=>	$param['comment'],
	        'add_timestamp' => date('Y-m-d H:i:s',time())
	    ))){
	        Log::error('create record err . ErrorNo=' . $this->getErrorNo() . ' ,ErrorInfo=' . $this->getErrorInfo());
	        return false;
	    }
	    return true;
	}
	
}