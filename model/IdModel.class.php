<?php

class IdModel extends Model{
	protected static $_user_id_offset = 1000;
	protected static $_authorization_code_id_offset = 100;
	protected static $_certification_id_offset = 1000;
	protected static $_trade_record_id_offset = 100;
	protected static $_trade_record_item_id_offset = 100;
	protected static $_bcs_trade_id_offset = 100;
	protected static $_bcs_transfer_id_offset = 100;
	protected static $_bcs_child_account_id_offset = 100;
	protected static $_bcs_register_id_offset = 100;
	protected static $_sit_no_offset = 100;
	protected static $_bcs_customer_id_offset = 100;

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
	
	public function getTradeRecordItemId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_trade_record_item'";
	    $this->execute($sql);
	    return (self::$_trade_record_item_id_offset + $this->db->insertId());
	}
	
	public function getBcsTradeId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_bcs_trade'";
	    $this->execute($sql);
	    return (self::$_bcs_trade_id_offset + $this->db->insertId());
	}

	//默认从1开始
	public function getMessageId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_message'";
		$this->execute($sql);
		return $this->db->insertId();
	}
	
	public function getBcsTransferId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_bcs_transfer'";
	    $this->execute($sql);
	    return (self::$_bcs_transfer_id_offset + $this->db->insertId());
	}
	
	public function getBcsChildAccountId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_bcs_child_account'";
	    $this->execute($sql);
	    return (self::$_bcs_child_account_id_offset + $this->db->insertId());
	}

	public function getBcsRegisterId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_bcs_register'";
		$this->execute($sql);
		return (self::$_bcs_register_id_offset + $this->db->insertId());
	}

	//获取席位编号
	public function getSitNo(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='sit_no'";
		$this->execute($sql);
		return ('DDMG'.str_pad(self::$_sit_no_offset+$this->db->insertId(),5,0,STR_PAD_LEFT));
	}
	
	public function getBcsCustomerId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='c_bcs_customer'";
	    $this->execute($sql);
	    return (self::$_bcs_customer_id_offset + $this->db->insertId());
	}
}