<?php

class IdModel extends Model{
	protected static $_user_id_offset = 1000;
	protected static $_authorization_code_id_offset = 100;
	protected static $_certification_id_offset = 1000;
	protected static $_trade_record_id_offset = 100;
	
	public function tableName(){
		return 'c_id';
	}
	
	public function getUserId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_user'";
		$this->execute($sql);
		return (self::$_user_id_offset + $this->db->insertId());
	}
	
	public function getAuthorizationCodeId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_authorization_code'";
	    $this->execute($sql);
	    return (self::$_authorization_code_id_offset + $this->db->insertId());
	}

	public function getCertificationId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_certification'";
		$this->execute($sql);
		return (self::$_certification_id_offset + $this->db->insertId());
	}
	public function getTradeRecordId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_trade_record'";
	    $this->execute($sql);
	    return (self::$_trade_record_id_offset + $this->db->insertId());
	}
	
}