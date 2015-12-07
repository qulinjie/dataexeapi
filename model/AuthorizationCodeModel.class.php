<?php

class AuthorizationCodeModel extends Model {
    
	public function tableName(){
		return 'c_authorization_code';
	}
	
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
	
}