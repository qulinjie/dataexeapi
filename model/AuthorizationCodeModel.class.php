<?php

class AuthorizationCodeModel extends Model {
    
	public function tableName(){
		return 'c_authorization_code';
	}
	
	public function getSearchCnt($params = array()){
	    
	    $params['is_delete'] = 1; // 1-正常 2-删除
	    
	    Log::notice('getSearchCnt ==== >>> params=' . json_encode($params) );
	    return $this->count(null, 'id', $params);
	}
	
	public function getSearchList($params = array(), $page = null, $count = null){
	    $model = $this->from();
	    
	    $where = [];
	    $fields = ['code','user_id','type','status'];
	    foreach ($fields as $key => $val){
	        if( !$params[$val] ){
	            continue;
	        }
	        switch ( $val ) {
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
	
	
}