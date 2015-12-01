<?php
/**
 * Router.class.php
 *
 * 获取网址的路由信息类
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Router.class.php 1.0 2012-01-18 21:35:01Z tommy $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Router {

    /**
     * 分析路由网址, 获取当前的Controller名及action名
     *
     * 通过对URL(网址)的分析,获取当前运行的controller和action,赋值给变量self::controller, 和self::action,
     * 方便程序调用,同时将URL中的所含有的变量信息提取出来 ,写入$_GET全局超级变量数组中.
     *
     * 注:这里的URL的有效部分是网址'?'之前的部分.'?'之后的部分不再被分析,因为'?'之后的URL部分完全属于$_GET正常调用的范畴.
     * 这里的网址分析不支持功能强大的路由功能,只是将网址中的'/'分隔开,经过简单地程序处理提取有用数据.
     * @access public
     * @return array
     */
    public static function Request() {
        //分析包含路由信息的网址
        if (isset($_SERVER['REQUEST_URI'])) {
            
        	$request_uri = $_SERVER['REQUEST_URI'];
            
            $base_url = Router::getBaseUrl();
            
            $base_url_len = strlen($base_url);
            //去掉DOIT_ROOT
            if(substr($request_uri, 0, $base_url_len) == $base_url) {
            	$start_pos = $base_url_len;
            }
            
            if(substr($request_uri, $base_url_len, 10) == 'index.php/') {
            	$start_pos += 10;
            }
            //去掉?后的内容
            $qmark_pos = strpos($request_uri, '?');
            if ($qmark_pos !== false) {
            	$request_uri = substr($request_uri, $start_pos, $qmark_pos - $start_pos);
            } else {
            	$request_uri = substr($request_uri, $start_pos);
            }
            //去掉最后一个'/'
            $request_uri_len = strlen($request_uri);
            if($request_uri[$request_uri_len - 1] == '/') {
            	$request_uri = substr($request_uri, 0, $request_uri_len - 1);
            }
            
			$tmp_arr = explode('/', $request_uri);
			if(count($tmp_arr) > 0){
				$controllerName = $tmp_arr[0];
				if('' == $controllerName) $controllerName = 'index';
				//params
				$params = array();
				for ($i = 1; $i < count($tmp_arr); $i ++){
					$params [] = urldecode(htmlspecialchars(trim($tmp_arr[$i])));
				}
				return array('controller' => ucfirst(strtolower($controllerName)), 'params' => $params);
			}
        }

        return array('controller' => DEFAULT_CONTROLLER, 'params' => array());
    }

    /**
     * 获取当前项目的根目录的URL
     *
     * 用于网页的CSS, JavaScript，图片等文件的调用.
     * @access public
     * @return string     根目录的URL. 注:URL以反斜杠("/")结尾
     */
    public static function getBaseUrl() {

        //处理URL中的//或\\情况,即:出现/或\重复的现象
        $url = str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));

        return (substr($url, -1) == '/') ? $url : $url . '/';
    }
    
    /**
     * 获取当前网页的域名和根目录
     */
    public static function getDomainAndBaseUrl(){
    	return 'http://' . $_SERVER['SERVER_NAME'] . self::getBaseUrl();
    }
}