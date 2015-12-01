<?php
/**
 * Request.class.php
 *
 * 获取HTTP的$POST, $_GET参数信息
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Request.class.php 1.3 2012-01-18 20:32:00Z tommy $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Request extends Base {

    /**
     * 获取并分析$_GET数组某参数值
     *
     * 获取$_GET的全局超级变量数组的某参数值,并进行转义化处理，提升代码安全.注:参数支持数组
     * @access public
     * @param string $string 所要获取$_GET的参数
     * @param string $defaultParam 默认参数, 注:只有$string不为数组时有效
     * @return string    $_GET数组某参数值
     */
    public static function get($string, $defaultParam = null) {

        if (!isset($_GET[$string])) {
            return is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam));
        }

        if (!is_array($_GET[$string])) {
            $getParams = htmlspecialchars(trim($_GET[$string]));
            return !is_null($getParams) ? $getParams : (is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam)));
        }

        foreach ($_GET[$string] as $key=>$value) {
            $getArray[$key] = htmlspecialchars(trim($value));
        }

        return $getArray;
    }

    /**
     * 获取并分析$_POST数组某参数值
     *
     * 获取$_POST全局变量数组的某参数值,并进行转义等处理，提升代码安全.注:参数支持数组
     * @access public
     * @param string $string    所要获取$_POST的参数
     * @param string $defaultParam 默认参数, 注:只有$string不为数组时有效
     * @return string    $_POST数组某参数值
     */
    public static function post($string, $defaultParam = null) {

        if (!isset($_POST[$string])) {
            return is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam));
        }

        if (!is_array($_POST[$string])) {
            $postParams = htmlspecialchars(trim($_POST[$string]));
            return !is_null($postParams) ? $postParams : (is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam)));
        }

        foreach ($_POST[$string] as $key=>$value) {
            $postArray[$key] = htmlspecialchars(trim($value));
        }

        return $postArray;
    }

    /**
     * 批量获取$_POST或$_GET数组参数值
     *
     * @access public
     * @param string $type 请求类型: post, get, cookie, request
     * @return array
     */
    public static function requestVars($type = 'post') {

        //初始化数组
        $paramArray = array();

        switch ($type) {
            case 'post':
                if (isset($_POST)) {
                    $keyArray = array_keys($_POST);
                    foreach ((array)$keyArray as $name) {
                        $paramArray[$name] = self::post($name);
                    }
                }
                break;

            case 'get':
                if (isset($_GET)) {
                    $keyArray = array_keys($_GET);
                    foreach ((array)$keyArray as $name) {
                        $paramArray[$name] = self::get($name);
                    }
                }
                break;

            case 'request':
                if (isset($_REQUEST)) {
                    $keyArray = array_keys($_GET);
                    foreach ((array)$keyArray as $name) {
                        $paramArray[$name] = ($_REQUEST[$name]) ? htmlspecialchars(trim($_REQUEST[$name])) : '';
                    }
                }
                break;
        }

        return $paramArray;
    }

    /**
     * 获取并分析 $_GET或$_POST全局超级变量数组某参数的值
     *
     * 获取并分析$_POST['参数']的值 ，当$_POST['参数']不存在或为空时，再获取$_GET['参数']的值。
     * @access public
     * @param string $string 所要获取的参数名称
     * @param string $defaultParam 默认参数, 注:只有$string不为数组时有效
     * @return string    $_GET或$_POST数组某参数值
     */
    public static function getParams($string, $defaultParam = null) {

        $paramValue = self::post($string, $defaultParam);

        //当$_POST[$string]值没空时
        return (!$paramValue) ? self::get($string, $defaultParam) : $paramValue;
    }

    /**
     * 获取PHP在CLI运行模式下的参数
     *
     * @access public
     * @param string $string 参数键值, 注:不支持数组
     * @param string $defaultParam 默认参数
     * @return string
     */
    public static function getCliParams($string , $defaultParam = null) {

        //参数分析
        if (!isset($_SERVER['argv'][$string])) {
            return is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam));
        }

        $cliParams = htmlspecialchars(trim($_SERVER['argv'][$string]));
        return ($cliParams) ? $cliParams : (is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam)));
    }
}