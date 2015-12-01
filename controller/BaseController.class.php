<?php
/**
 * @file:  BaseController.class.php
 * @brief:  控制器基类, 
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-08-22
 */

abstract class BaseController extends Controller
{
	/**
	 * @brief:  获取$_SERVER['REQUEST_URI'] 里?号及其左边的部分
	 * @return:  
	 */
	protected function getUriRoot()
	{
		$pos = strpos( $_SERVER['REQUEST_URI'], '?' );
		if ( $pos!==false ) {
			$uri_root  = substr( $_SERVER['REQUEST_URI'], 0, $pos );
		}else{
			$uri_root  = $_SERVER['REQUEST_URI'];
		}
		return $uri_root . '?';
	}

	/**
	 * @brief:  从当前的URI中过滤掉指定的GET参数，然后&连接后的URL参数字符串
	 * @param:  $filterParam  
	 * @example:  
	 * @return:  
	 */
	protected function getQueryString( $filterParam=[] )
	{
		if ( !is_array( $filterParam ) ) {
			$filterParam = [ $filterParam ];
		}
		$temp = [];
		foreach ( $_GET as $k=>$v ) {
			if ( !in_array( $k, $filterParam ) && $v !== '' ) {
				$temp[] = "{$k}={$v}";
			}
		}
		return  join( '&', $temp ) . '&';
		/*
		$queryString = $_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '';
		$queryString = preg_replace("/&?{$filterParam}=[0-9]/", '', $queryString);
		$queryString = trim($queryString, '&');
	    ($queryString) && ($queryString = $queryString . '&');
		return  $queryString;
		 */
	}

	/**
	 * @brief:  输出错误提示并返回
	 * @param:  $messge
	 * @return:  
	 */
	protected function fail( $messge, $url='' )
	{
		if ( !$url ) {
			die( "<script>alert('{$messge}');history.go(-1);</script>" );
		}
		die( "<script>alert('{$messge}'); window.location='{$url}';</script>" );
	}

	/**
	 * @brief:  成功
	 * @param:  $messge
	 * @return:  
	 */
	protected function success( $messge, $url )
	{
		die( "<script>alert('{$messge}'); window.location='{$url}';</script>" );
	}


	/**
	 * @brief:  获取登录的卖家ID  -- 先用着，其实有下面那个总的数据，这个有点多余
	 * @return:  
	 */
	protected function getLoginSellerId()
	{
		$user = $this-> getLoginSeller();
		return $user['id'];
		/*
		$session =  $this->instance('session');	
		$loginUser = $session->get( 'loginUser' );
		return $loginUser['id'];
		 */
	}

	protected function getLoginSellerAccount()
	{
		$user = $this-> getLoginSeller();
		return $user['tel'];
		/*
		$session =  $this->instance('session');	
		$loginUser = $session->get( 'loginUser' );
		return $loginUser['tel'];
		 */
	}

	/**
	 * @brief:  获取登录的卖家信息
	 * @return:  
	 */
	protected function getLoginSeller()
	{
		$res =  Controller::model('user')->isLogin();	
		$user = $res['data'];
		unset( $user['password'] );
		return $user;
		//$session =  $this->instance('session');	
		//return $session->get( 'loginUser' );
	}

	/**
	 * @brief:  把原框架的分页方式，在包了一些，解决翻页跟参数的问题。
	 * @param:  $page
	 * @param:  $total
	 * @param:  $numPerPage
	 * @return:  
	 */
	protected function getPageHtml( $page, $total, $numPerPage=10 )
	{
		if ( !$page || !$total ) {
			return '';
		}
		$pager = $this->instance( 'pager' );

		//$new_uri = $this->filterUri( 'page' );

		$uri_root = $this-> getUriRoot();
		$queryString = $this-> getQueryString('page');

		//return $pager->total( $total )->num( $numPerPage )->page( $page )->url( "{$new_uri}page=" )->output();
		return $pager->total( $total )->num( $numPerPage )->page( $page )->url( "{$uri_root}{$queryString}page=" )->output();
	}


	/**
	 * @brief:  输出规定格式的JSON错误响应
	 * @param:  $msg
	 * @param:  $data
	 * @return:  
	 */
	protected function jsonFail( $msg='', $data=[] )
	{
		$this->outJson( -1, $msg, $data );
	}
	/**
	 * @brief:  输出规定格式的JSON 成功响应
	 * @param:  $msg
	 * @param:  $data
	 * @return:  
	 */
	protected function jsonSuccess( $msg='', $data=[] )
	{
		$this->outJson( 0, $msg, $data );
	}
	/**
	 * @brief:  结算进程前，输出接JSON结果
	 * @param:  $code
	 * @param:  $msg
	 * @param:  $data
	 * @return:  
	 */
	protected function outJson( $code=-1, $msg='', $data=[] )
	{
	  die( json_encode( ['code'=>$code, 'msg'=>$msg, 'data'=>$data]) );
	}

}
