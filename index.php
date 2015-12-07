<?php
/**
 * doit.class.php
 *
 * DoitPHP核心类,并初始化框架的基本设置
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: doit.class.php 1.5 2012-10-01 12:00:01Z tommy $
 * @package core
 * @since 1.0
 */
define('IN_DOIT', 1);

define('DOIT_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/**
 * 定义错误提示级别
 */
error_reporting(E_ALL^E_NOTICE);


/**
 * 设置程序开始执行时间.根据实际需要,自行开启,如开启去掉下面的//
 */
//define('DOIT_START_TIME',microtime(true));

if (!defined('CORE_DIR')) {
	define('CORE_DIR', DOIT_ROOT . 'core' . DIRECTORY_SEPARATOR);
}

/**
 * 项目缓存文件存放目录的路径
 */
if (!defined('CACHE_DIR')) {
    define('CACHE_DIR', DOIT_ROOT . 'cache' . DIRECTORY_SEPARATOR);
}

/**
 * 项目运行日志文件存放目录的路径
 */
if (!defined('LOG_DIR')) {
    define('LOG_DIR', DOIT_ROOT . 'log' . DIRECTORY_SEPARATOR);
}

/**
 * controller dir
 */

if(!defined('CONTROLLER_DIR')) {
	define('CONTROLLER_DIR', DOIT_ROOT . 'controller' . DIRECTORY_SEPARATOR);
}



if(!defined('MODEL_DIR')) {
	define('MODEL_DIR', DOIT_ROOT . 'model' . DIRECTORY_SEPARATOR);
}



if(!defined('CONFIG_DIR')) {
	define('CONFIG_DIR', DOIT_ROOT . 'conf' . DIRECTORY_SEPARATOR);
}

if(!defined('VIEW_DIR')) {
	define('VIEW_DIR', DOIT_ROOT . 'view' . DIRECTORY_SEPARATOR);
}



/**
 * 设置是否开启调试模式.开启后,程序运行出现错误时,显示错误信息,便于程序调试.
 * 默认为关闭,如需开启,将下面的false改为true.
 */
if (!defined('DOIT_DEBUG')) {
    define('DOIT_DEBUG', false);
}

/**
 * 设置URL的Rewrite功能是否开启,如开启后,需WEB服务器软件如:apache或nginx等,要开启Rewrite功能.
 * 默认为关闭,如需开启,只需将false换成true.
 */
if (!defined('DOIT_REWRITE')) {
    define('DOIT_REWRITE', false);
}

/**
 * 设置日志写入功能是否开启
 * 默认为开启,如需关闭,只需将true换成false.
 */
if (!defined('DOIT_LOG')) {
    define('DOIT_LOG', true);
}

/**
 * 设置时区,默认时区为东八区(中国)时区.
 * 如需更改时区,将'Asia/ShangHai'设置你所需要的时区.
 */
if (!defined('DOIT_TIMEZONE')) {
    define('DOIT_TIMEZONE', 'Asia/ShangHai');
}

/**
 * 设置系统默认的controller名称,默认为:Index
 * 如需更改,将Index换成所需要的.
 * 注:为提高不同系统平台的兼容性,名称首字母要大写,其余小写.
 */
if (!defined('DEFAULT_CONTROLLER')) {
    define('DEFAULT_CONTROLLER', 'Index');
}


/**
 * 定义网址路由的分割符
 * 注：分割符不要与其它网址参数等数据相冲突
 */
if (!defined('URL_SEGEMENTATION')) {
    define('URL_SEGEMENTATION', '/');
}


/**
 * 定义入口文件名
 */
if (!defined('ENTRY_SCRIPT_NAME')) {
    define('ENTRY_SCRIPT_NAME', 'index.php');
}



/**
 * 加载路由网址分析文件
 */
require_once CORE_DIR . 'Router.class.php';

/**
 * Doitphp框架核心全局控制类
 *
 * 用于初始化程序运行及完成基本设置
 * @author tommy <streen003@gmail.com>
 * @version 1.0
 */
abstract class doit {

    /**
     * 控制器(controller)
     *
     * @var string
     */
    public static $controller;

    /**
     * 动作(action)
     *
     * @var string
     */
    public static $params;

    /**
     * POST数据
     * 
     */
    public static $req_data = array();
    
    /**
     * 应用头部数据
     */
    public static $caller = 'none';
    public static $callee = 'none';
    public static $timestamp = 0;
    public static $eventid = 0;
    
    /**
     * response data
     */
    public static $res = array();
    public static $res_str = 'empty';

    /**
     * 对象注册表
     *
     * @var array
     */
    public static $_objects = array();

    /**
     * 载入的文件名(用于PHP函数include所加载过的)
     *
     * @var array
     */
    public static $_incFiles = array();

    /**
     * 项目运行函数
     *
     * 供项目入口文件(index.php)所调用,用于启动框架程序运行
     * @access public
     * @return object
     */
    public static function run() {
        //定义变量_app
        static $_app = array();

        //分析URL,获取controller和action的名称
        $url_params  = Router::Request();
        
        self::$controller = $url_params['controller'];
        self::$params     = $url_params['params'];
        
        $data = json_decode($url_params['data'],true);
        Log::notice("url_params====================>>>data=##" . json_encode($data) . "##");
        
        //判断是否是
        if(self::$params[0] != 'getbill' && self::$params[0] != 'getcallstatus'){
            foreach ( ['caller', 'callee', 'timestamp', 'eventid'] as $val ){
                if ( !$data[$val] ) {
                    Log::error('require header info: caller, callee, timestamp and eventid');
                    EC::fail(EC_PAR_BAD);
                }
                self::$$val = $data[$val];
            }
        }
        self::$req_data	= $data['data'] ? $data['data'] : [];
        $appId = self::$controller . '_' . implode('_', self::$params) . $url_params['data'];
        
        Log::notice("isset====================>>>appId=##" . json_encode($appId) . "##");
        if (!isset($_app[$appId])) {

            //通过实例化及调用所实例化对象的方法,来完成controller中action页面的加载
            $controller = self::$controller . 'Controller';

            //加载基本文件:Base,Controller基类
            self::loadFile(CORE_DIR . 'Base.class.php');
            self::loadFile(CORE_DIR . 'Controller.class.php');

            //加载当前要运行的controller文件
            if (is_file(CONTROLLER_DIR . $controller . '.class.php')) {
                //当文件在controller根目录下存在时,直接加载.
                self::loadFile(CONTROLLER_DIR . $controller . '.class.php');
            } else {
                EC::page_not_found();
            }

            //创建一个页面控制对象
            $appObject = new $controller();

            Log::notice("data====================>>>req_data=##" . json_encode(self::$req_data) . "##");
            $_app[$appId] = $appObject->handle(self::$params, self::$req_data);
        }
        Log::notice("is-not-set====================>>>appId=##" . json_encode($appId) . "##");
        return $_app[$appId];
    }

    /**
     * 获取当前运行的controller名称
     *
     * @example $controllerName = doit::getControllerName();
     * @access public
     * @return string controller名称(字母全部小写)
     */
    public static function getControllerName() {

        return strtolower(self::$controller);
    }

   public static function getParams(){
   		return self::$params;
   }
    
    
    /**
     * 返回唯一的实例(单例模式)
     *
     * 程序开发中,model,module, widget, 或其它类在实例化的时候,将类名登记到doitPHP注册表数组($_objects)中,当程序再次实例化时,直接从注册表数组中返回所要的对象.
     * 若在注册表数组中没有查询到相关的实例化对象,则进行实例化,并将所实例化的对象登记在注册表数组中.此功能等同于类的单例模式.
     *
     * 注:本方法只支持实例化无须参数的类.如$object = new pagelist(); 不支持实例化含有参数的.
     * 如:$object = new pgelist($total_list, $page);
     *
     * <code>
     * $object = doit::singleton('pagelist');
     * </code>
     *
     * @access public
     * @param string $className  要获取的对象的类名字
     * @return object 返回对象实例
     */
    public static function singleton($className) {

        //参数分析
        if (!$className) {
            return false;
        }

        $key = trim($className);

        if (isset(self::$_objects[$key])) {
            return self::$_objects[$key];
        }

        return self::$_objects[$key] = new $className();
    }

    /**
     * 静态加载文件(相当于PHP函数require_once)
     *
     * include 以$fileName为名的php文件,如果加载了,这里将不再加载.
     * @param string $fileName 文件路径,注:含后缀名
     * @return boolean
     */
    public static function loadFile($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        //判断文件有没有加载过,加载过的直接返回true
        if (!isset(self::$_incFiles[$fileName])) {

            //分析文件是不是真实存在,若文件不存在,则只能...
            if (!is_file($fileName)) {
                //当所要加载的文件不存在时,错误提示
               	EC::page_not_found();
            }

            include_once $fileName;
            self::$_incFiles[$fileName] = true;
        }

        return self::$_incFiles[$fileName];
    }
}

/**
 * 自动加载引导文件的加载
 */
include_once CORE_DIR . 'AutoLoad.class.php';

/**
 * 调用SPL扩展,注册__autoload()函数.
 */
spl_autoload_register(array('AutoLoad', 'index'));


/**
 * 使用redis存储session
 */
session_redis::init();
/**
 * 防重复提交
 */
//$GLOBALS['processlock_obj'] = new processlock($_SERVER['REQUEST_URI']);
//$GLOBALS['processlock_obj']->lock();

/**
 * 加载error code
 */
EC::load();

function getPostStr(){
    //获取POST数据
    $post_data_1 = file_get_contents("php://input");
    $post_data_2 = $GLOBALS['HTTP_RAW_POST_DATA'];
    $post_data = $post_data_1 == '' ? $post_data_2 : $post_data_1;
//     Log::notice("req_data====================>>>post_data=" . json_encode($post_data));
    return $post_data;
}

//记录请求
$request_log_obj = new request_log($_SERVER['REQUEST_URI'], getPostStr());

/**
 * 加载error code
function getPostStr()
{
	$post_data_1 = file_get_contents("php://input");
	$post_data_2 = $GLOBALS['HTTP_RAW_POST_DATA'];
	return  ($post_data_1 != '') ? $post_data_1 : $post_data_2;
}
$request_log_obj = new request_log($_SERVER['REQUEST_URI'], getPostStr());
 */


/**
 * 启动网站进程
 */
doit::run();
