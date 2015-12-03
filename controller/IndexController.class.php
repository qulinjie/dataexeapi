<?php
class IndexController extends Controller 
{
	public function handle( $params=[] ) 
	{
		$this->display('index');
	}

	public function init(  )
	{
// 		PassportController::checkLogin();
	}

}
