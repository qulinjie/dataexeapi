<?php

class AuthorizationCodeModel extends Model {
    
	public function tableName(){
		return 'c_authorization_code';
	}
	
	public function getSearchCnt($params = array()){
	    /* $keys = array();
	    $values = array();
	
	    $keys[] = 'is_partner = ?';
	    $values[] = UserModel::$_is_partner_yes;
	
	    if(-1 != $params['enabled_status']){
	        $keys[] = 'enabled_status in ( '.$params['enabled_status'].' ) and \'1\'=? ';
	        $values[] = '1';
	    } else {
	        $keys[] = 'enabled_status in ( ' . UserModel::$_enabled_status_enabled . ',' . UserModel::$_enabled_status_disabled.' )  and \'1\'=? ';
	        $values[] = '1';
	    }
	
	    if($params['tel']){
	        $keys[] = 'tel=?';
	        $values [] = $params['tel'];
	    }
	
	    if($params['name']){
	        $keys[] = 'name like ?';
	        $values [] = '%' . $params['name'] . '%';
	    }
	    if($params['company']){
	        $keys[] = 'company like ?';
	        $values [] = '%' . $params['company'] . '%';
	    }
	
	    if($params['manager_id']){
	        $keys[] = 'manager_id=?';
	        $values [] = $params['manager_id'];
	    }
	     
	    if($params['time1']){
	        $keys[] = 'add_timestamp >=?';
	        $values [] = $params['time1'];
	    }
	    if($params['time2']){
	        $keys[] = 'add_timestamp <=?';
	        $values [] = $params['time2'];
	    } */
	    
	    $params['is_delete'] = 1; // 1-正常 2-删除
	    
	    Log::notice('getSearchCnt ==== >>> params=' . json_encode($params) );
	    return $this->count(null, 'id', $params);
	}
	
	public function getSearchList($params = array(), $page = null, $count = null){
	    $model = $this->from();
	    /* $model->where('is_partner = ' . UserModel::$_is_partner_yes);
	
	    if(-1 != $params['enabled_status']){
	        $model->where('enabled_status in (' . $params['enabled_status'] . ')');
	    } else {
	        $model->where('enabled_status in (' . UserModel::$_enabled_status_enabled . ',' . UserModel::$_enabled_status_disabled . ')');
	    }
	
	    if($params['manager_id']){
	        $model->where('manager_id="' . $params['manager_id'] . '"');
	    }
	     
	    if($params['tel']){
	        $model->where('tel="' . $params['tel'] . '"');
	    }
	
	    if($params['name']){
	        $model->where("name like \"%" . $params['name'] . "%\"");
	    }
	    if($params['company']){
	        $model->where("company like \"%" . $params['company'] . "%\"");
	    }
	     
	    if($params['time1']){
	        $model->where('add_timestamp >="' . $params['time1'] . '"');
	    }
	    if($params['time2']){
	        $model->where('add_timestamp <="' . $params['time2'] . '"');
	    } */
	    
	    $where = [];
	    $fields = ['user_id','type','status'];
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
	
	
	/* 
	public function getSearchCnt( $condition )
	{
	    if ( !$condition ) {
	        Log::error( 'condition is empty . ' );
	        EC::fail( EC_PAR_BAD );
	    }
	
	    unset( $condition['count'] );
	    unset( $condition['page'] );
	    
	    $condition['is_delete'] = 1;
	
	    $data = $this->count( null, '1', $condition );
	    return $data;
	}
	
	public function getSearchList( $condition )
	{
	    $where = [];
	    $fields = ['user_id','type','status','start_date','end_date'];
	    foreach ($fields as $key => $val){
	        if( !$condition[$val] ){
	            continue;
	        }
	        switch ( $val ) {
	            default:
	                $where[] = "{$val}='{$condition[$val]}'";
	                break;
	        }
	    }
	    $page = intval($condition['page']) < 1 ? 1 : intval($condition['page']);
	    $count = intval($condition['count']) < 1 ? 1 : intval($condition['count']);
	
	    $where[] = 'is_delete=1'; // 1-正常 2-删除
	    
	    Log::notice('getSearchList ==== >>> where=' . json_encode($where) );
	    $data = $this->getAuthCodeList( $where, null, $page, $count );
	    return $data;
	}
	
	public function getAuthCodeList(  $where=[], $fields=[], $page=0, $count=0  )
	{
	    if ( !$where ) {
	        return false;
	    }
	
	    if ( $fields ) {
	        $this-> from( null, $fields );
	    }else{
	        $this-> from();
	    }
	
	    $this-> where( $where );
	
	    if ( $page && $count ) {
	        $this-> pageLimit( $page, $count );
	    }
	
	    $this-> order( 'add_timestamp DESC' );
	
	    $data = $this->select();
	    return $data;
	}
	 */
}