<?php
/**
 * @file:  BaseModel.class.php
 * @brief:  基类
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-09-10
 */

abstract class BaseModel
{
	/**
	 * @brief:  获取COOKIE 文件存放位置
	 * @return:  
	 */
	protected  function getCookieFile( $user_id )
	{
		return "curl_cookie_{$user_id}.txt"; //
		//return 'D:\wamp\www\DDMG2\ddmg_server\trunk\xxx.txt'; //
	}

	/**
	 * @brief:  向接口发送数据
	 * @param:  $url
	 * @param:  $data
	 * @param:  $header
	 * @param:  $proxy
	 * @param:  $expire
	 * @return:  
	 */
	protected function send( $id, $url, $data='', $header=[], $proxy=null, $expire=3600 )
	{
		if ( !$url ) {
			return false;
		}
		$cookieFile =  $this->getCookieFile( $id );

		//分析是否开启SSL加密
		$ssl         = substr($url, 0, 8) == 'https://' ? true : false;

		//读取网址内容
		$ch = curl_init();
		//设置代理
		if (!is_null($proxy)) {
			curl_setopt ($ch, CURLOPT_PROXY, $proxy);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		if ($ssl) {
			// 对认证证书来源的检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			// 从证书中检查SSL加密算法是否存在
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		}
		
		//cookie设置
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

		//设置请求header
		if( !empty($header) ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		//发送一个常规的Post请求
		curl_setopt($ch, CURLOPT_POST, true);
		//Post提交的数据包
		curl_setopt($ch,  CURLOPT_POSTFIELDS, json_encode($data));

		//使用自动跳转
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

		$content = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($httpCode != 200) {
			echo 'http code error, code:' . $httpCode . '<BR>';
			return false;
		}
		return $content;
	}

	/**
	 * @brief:  获取接口URL
	 * @param:  $interface
	 * @return:  
	 */
	protected  function getUrl( $interface='' )
	{
		return 'http://120.25.1.102/ddmg_server/'.$interface; // mark
		//return 'http://ddmg.com/'.$interface; // mark
	}

	/**
	 * @brief:  加密数据
	 * @param:  $password
	 * @return:  
	 */
	protected function ppwd( $password )
	{
		$public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnqqHspRsIm9MlnGiEdpnux3D5
G9jrVqYP7gl+OuhtZKxhp1CiQuKxmBkiF5YdWGutAzBdA0hWd4k+vbTSDuJmVcIa
krb0/MkQxbg1YPjVjtBv7i0sJJOFv/A0oLNEJjuyiOMWSv30d2VkvU/3of/mnW33
Kb/4PN/nOI8h1rj0IQIDAQAB
-----END PUBLIC KEY-----';
		$pu_key = openssl_pkey_get_public($public_key);
		$crypted = '';
		openssl_public_encrypt($password, $crypted,$pu_key);
		$input = base64_encode($crypted);
		return $input;
	}

	protected function getLoginId()
	{
		$session =  Controller::instance('session');	
		$loginUser = $session->get( 'loginUser' );
		return $loginUser['account'];
	}
}
