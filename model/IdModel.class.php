<?php

class IdModel extends Model{
	protected static $_user_id_offset = 1000;
	protected static $_addr_id_offset = 1000;
	protected static $_order_id_offset = 1000;
	protected static $_order_item_id_offset = 1000;
	protected static $_category_id_offset = 0;
	protected static $_material_id_offset = 0;
	protected static $_product_id_offset = 0;
	protected static $_technic_id_offset = 0;
	protected static $_size_id_offset = 0;
	protected static $_item_id_offset = 100;
	protected static $_factory_id_offset = 0;
	protected static $_cast_tender_id_offset = 100;
	
	public function tableName(){
		return 'c_id';
	}
	
	public function getUserId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='user_id'";
		$this->execute($sql);
		return (self::$_user_id_offset + $this->db->insertId());
	}
	
	public function getAddrId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='address_id'";
		$this->execute($sql);
		return (self::$_addr_id_offset + $this->db->insertId());
	}

	public function getOrderId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='order_id'";
		$this->execute($sql);
		return (self::$_order_id_offset + $this->db->insertId());
	}

	public function getOrderItemId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='order_item_id'";
		$this->execute($sql);
		return (self::$_order_item_id_offset + $this->db->insertId());
	}

	public function getCategoryId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='category_id'";
		$this->execute($sql);
		return (self::$_category_id_offset + $this->db->insertId());
	}

	public function getMaterialId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='material_id'";
		$this->execute($sql);
		return (self::$_material_id_offset + $this->db->insertId());
	}

	public function getProductId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='product_id'";
		$this->execute($sql);
		return (self::$_product_id_offset + $this->db->insertId());
	}

	public function getTechnicId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='technic_id'";
		$this->execute($sql);
		return (self::$_technic_id_offset + $this->db->insertId());
	}

	public function getSizeId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='size_id'";
		$this->execute($sql);
		return (self::$_size_id_offset + $this->db->insertId());
	}

	public function getItemId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='item_id'";
		$this->execute($sql);
		return (self::$_item_id_offset + $this->db->insertId());
	}

	public function getFactoryId(){
		$sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='factory_id'";
		$this->execute($sql);
		return (self::$_factory_id_offset + $this->db->insertId());
	}
	
	public function getCastTenderId(){
	    $sql = "update " . $this->tableName() . " set id = LAST_INSERT_ID(id +1) where name='cast_tender_id'";
	    $this->execute($sql);
	    return (self::$_cast_tender_id_offset + $this->db->insertId());
	}
}