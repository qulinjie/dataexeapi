<?php
if (!defined('IN_DOIT')) {
	exit();
}

/**
 * 保证当前session只能进行一个动作，防止动作重复提交
 * @author paco
 *
 */

class processlock extends Base {
	
	private $action;
	
	function __construct($action){
		$this->action = $action;
	}
	
	public function lock(){
		Log::notice('lock action:' . $this->action);
		$encrypt_obj = Controller::instance('encrypt');
		if(false === $encrypt_obj->processTokenCode($this->action)){
			Log::error('duplicate action:' . $this->action);
			EC::fail(EC_PRD_ACT);
		}
	}
	
	public function unlock(){
		Log::notice('unlock action:' . $this->action);
		$encrypt_obj = Controller::instance('encrypt');
		$encrypt_obj->deleteProcessTokenCode($this->action);
	}
}