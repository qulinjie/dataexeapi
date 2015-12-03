<?php
/**
 * cookie class file
 *
 * 用于cookie处理操作
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: cookie.class.php 1.3 2011-11-13 20:52:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class cookie extends Base {

    /**
     * 判断cookie变量是否存在
     *
     * @access public
     * @param string $cookieName    cookie的变量名
     * @return boolean
     */
    public static function is_set($cookieName) {

        if (!$cookieName) {
            return false;
        }

        return isset($_COOKIE[$cookieName]);
    }

    /**
     * 获取某cookie变量的值
     *
     * 获取的数值是进过64decode解密的,注:参数支持数组
     * @access public
     * @param string $cookieName    cookie变量名
     * @return string
     */
    public static function get($cookieName) {

        if (!$cookieName) {
            return false;
        }

        return isset($_COOKIE[$cookieName]) ? unserialize(base64_decode($_COOKIE[$cookieName])) : false;
    }

    /**
     * 设置某cookie变量的值
     *
     * 注:这里设置的cookie值是经过64code加密过的,要想获取需要解密.参数支持数组
     * @access public
     * @param string $name         cookie的变量名
     * @param string $value     cookie值
     * @param intger $expire    cookie所持续的有效时间,默认为一小时.(这个参数是时间段不是时间点,参数为一小时就是指从现在开始一小时内有效)
     * @param string $path        cookie所存放的目录,默认为网站根目录
     * @param string $domain    cookie所支持的域名,默认为空
     * @return void
     */
    public static function set($name, $value, $expire = null, $path = null, $domain = null) {

        //参数分析.
        $expire   = is_null($expire) ? time()+3600 : time()+$expire;
        if (is_null($path)) {
            $path = '/';
        }

        //数据加密处理.
        $value    = base64_encode(serialize($value));
        setcookie($name, $value, $expire, $path, $domain);
        $_COOKIE[$name] = $value;
    }

    /**
     * 删除某个Cookie值
     *
     * @access public
     * @param string $name    cookie的变量值
     * @return void
     */
    public static function delete($name, $path = null, $domain = null) {

        self::set($name, '', '-3600', $path, $domain);

        unset($_COOKIE[$name]);
    }

    /**
     * 清空cookie
     *
     * @access public
     * @return void
     */
    public static function clear($path = null, $domain = null) {

    	foreach ($_COOKIE as $k => $v){
    		self::set($k, '', '-3600', $path, $domain);
    	}
    	
        unset($_COOKIE);
    }

    /**
     * log cookie
     */
    public static function log(){
        Log::notice('cookie_data:' . var_export($_COOKIE, true));
    }
    
}