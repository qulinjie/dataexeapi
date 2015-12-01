<?php
/**
 * curl class file
 *
 * 常用的CURL操作
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: curl.class.php 1.3 2011-11-13 20:53:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class curl extends Base {

    /**
     * 用CURL模拟获取网页页面内容
     *
     * @param string $url     所要获取内容的网址
     * @param array  $data        所要提交的数据
     * @param string $proxy   代理设置
     * @param integer $expire 时间限制
     * @return string
     *
     * @example
     *
     * $proxy = '192.168.1.110:2010';
     * $url = 'http://www.doitphp.com/';
     *
     * $curl = new curl();
     * $curl -> do_get_content($url, $proxy);
     */
    public static function getRequest($url, $data = array(), $header = array(), $proxy = null, $expire = 30) {
    	Log::notice('get req data:' . var_export($data, true));
        //参数分析
        if (!$url) {
            return false;
        }
        if (!is_array($data)) {
            $data = (array)$data;
        }

        //cookie file
        $cookieFile = CACHE_DIR . 'temp/' . md5('doitphp_curl') . '.txt';

        //分析是否开启SSL加密
        $ssl = substr($url, 0, 8) == 'https://' ? true : false;

        //读取网址内容
        $ch = curl_init();

        //设置代理
        if (!is_null($proxy)) {
            curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        }

        //分析网址中的参数
        $paramUrl = http_build_query($data, '', '&');
        $extStr   = (strpos($url, '?') !== false) ? '&' : '?';
        $url      = $url . (($paramUrl) ? $extStr . $paramUrl : '');

        curl_setopt($ch, CURLOPT_URL, $url);

        Log::notice('url:' . $url);
        if ($ssl) {
            // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        }

        //cookie设置
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

        //设置浏览器
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        //设置请求header
        if(! empty($header)) {
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        Log::notice('get res data:' . $content);
     	if($httpCode != 200) {
        	Log::error('http code error, code:' . $httpCode);
        	Log::error('content:' . $content);
        	return false;
        }

        return $content;
    }

    /**
     * 用CURL模拟提交数据
     *
     * @param string $url        post所要提交的网址
     * @param array  $data        所要提交的数据
     * @param string $proxy        代理设置
     * @param integer $expire    所用的时间限制
     * @return string
     */
    public static function postRequest($url, $data = '', $header = array(), $proxy = null, $expire = 30) {

    	Log::notice('post req data:' . $data);
        //参数分析
        if (!$url) {
            return false;
        }

        //cookie file
        $cookieFile = CACHE_DIR . 'temp/' . md5('doitphp_curl') . '.txt';

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

        //设置浏览器
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //设置请求header
        if(! empty($header)) {
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        //发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POST, true);
        //Post提交的数据包
        curl_setopt($ch,  CURLOPT_POSTFIELDS, $data);

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::notice('post res data:' . $content);
        if($httpCode != 200) {
        	Log::error('http code error, code:' . $httpCode);
        	return false;
        }
        return $content;
    }
}