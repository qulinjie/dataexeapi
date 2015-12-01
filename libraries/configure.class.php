<?php
/**
 * configure.class.php
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: configure.class.php 1.0 2012-1-16 23:20:13Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class configure extends Base {

	/**
	 * 存贮配置内容的缓存文件名
	 *
	 * @var string
	 */
	public static $_fileName = 'doitConfigure';

    /**
     * 设置配置文件
     *
     * @access public
     * @param string $key 键值
     * @param string $value 设置的内容
     * @param stirng $fileName 配置文件名
     * @return boolean
     */
	public static function set($key, $value = null, $fileName = null) {

		//参数分析
		if (!$key) {
			return false;
		}
		if (!is_null($fileName)) {
			self::$_fileName = $fileName;
		}
		//获取配置文件内容
		$configArray = self::get(null, self::$_fileName);
		$configArray[$key] = $value;

		//将内容写入缓存文件
		return cache_php::set(self::$_fileName, $configArray);
	}

	/**
	 * 获取配置文件内容
	 *
	 * @access public
	 * @param string $key 键值
	 * @param stirng $fileName 配置文件名
	 * @return array|string
	 */
	public static function get($key = null, $fileName = null) {

		//参数分析
		if (!is_null($fileName)) {
			self::$_fileName = $fileName;
		}

		//获取配置文件内容
		$configArray = cache_php::get(self::$_fileName);

		if (!$configArray) {
			return array();
		}

		return is_null($key) ? $configArray : $configArray[$key];
	}
}