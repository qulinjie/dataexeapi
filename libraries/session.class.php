<?php
/**
 * session class file
 *
 * 处理session操作
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: session.class.php 1.3 2011-11-13 21:19:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class session extends Base {

    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct() {

        session_start();
        register_shutdown_function(array($this,'close'));
    }

    public function log(){
    	Log::notice('session data:' . var_export($_SESSION, true));
    }
    
    /**
     * 设置session变量的值
     *
     * @access public
     * @param string $key    session变量名
     * @param string $value    session值
     * @return void
     */
    public static function set($key, $value) {

        $_SESSION[$key]=$value;
    }

    /**
     * 获取某session变量的值
     *
     * @access public
     * @param string $key    session变量名
     * @return mixted
     */
    public static function get($key) {

        if (!isset($_SESSION[$key])) {
            return false;
        }

        return $_SESSION[$key];
    }

    /**
     * 删除某session的值
     *
     * @access public
     * @return boolean
     */
    public static function delete($key) {

        if (!isset($_SESSION[$key])){
            return false;
        }

        unset($_SESSION[$key]);

        return true;
    }

    /**
     * 清空session值
     *
     * @access public
     * @return void
     */
    public static function clear(){

        $_SESSION = array();
    }

    /**
     * 注销session
     *
     * @access public
     * @return void
     */
    public static function destroy() {

        if (session_id()){
            unset($_SESSION);
            session_destroy();
        }
    }

    /**
     * 当浏览器关闭时,session将停止写入
     *
     * @access public
     * @return void
     */
    public static function close(){

        if (session_id()) {
            session_write_close();
        }
    }

    /**
     * 获取session id 名称
     *
     * @access public
     * @return string
     */
    public static function get_name() {

        return session_name();
    }

    /**
     * 获取session id
     *
     * @access public
     * @return string
     */
    public static function get_id( ){

        return session_id();
    }

    /**
     * 设置session_name.
     *
     * @access public
     * @return void
     */
    public function setName($value) {

        session_name($value);
    }

    /**
     * 设置session_id.
     *
     * @param string $id
     * @return void
     */
    public static function set_id($id){

        session_id($id);
    }

    /**
     * 设置session文件的存放路径.
     *
     * @access public
     * @param string $value    session文件所存放的路径
     * @return void
     */
    public static function set_save_path($value) {

        if (!is_dir($value)) {
            Controller::halt('The path:' . $value . ' is not a valid directory!');
        }

        session_save_path($value);
    }

    /**
     * 获取session文件存放路径.
     *
     * @access public
     * @return void
     */
    public static function get_session_path() {

        return session_save_path();
    }

    /*
     * 检验session_start是否开启.
     *
     * @return void
     */
    public static function is_start() {

        return session_id() ? true : false;
    }

    /**
     * 检验session里有该session值.
     *
     * @param string $key
     * @return mixted
     */
    public static function is_set($key){

        if (!session_id()){
            return false;
        }

        return isset($_SESSION[$key]);
    }

    /**
     * 检验session有效时间.
     *
     * @access public
     * @return intger
     */
    public static function getTimeout() {

        return (int)ini_get('session.gc_maxlifetime');
    }

    /**
     * 设置session有最大存活时间.
     *
     * @param string $value
     * @return void
     */
    public static function setTimeout($value) {

        ini_set('session.gc_maxlifetime',$value);
    }
}