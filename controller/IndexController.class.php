<?php
class IndexController extends Controller 
{
	public function handle( $params=[] ) 
	{
		// 首页显示
		// 待审核订单多少个，带连接跳转
		// 待
		$this->display('index');
	}

	public function init(  )
	{
		PassportController::checkLogin();
	}

}
