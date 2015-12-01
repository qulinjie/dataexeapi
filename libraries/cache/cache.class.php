<?php
/**
 * cache.class.php
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: cache.class.php 1.0 2012-01-31 22:30:13Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class cache extends Base {

    /**
     * 工厂模式实例化常用缓存类
     *
     * @access public
     * @param string $adapter 缓存类型
     * @param array $params 参数
     * @return object
     */
    public static function factory($adapter, $options = null) {

        //参数分析
        if (!$adapter) {
            return false;
        }
        $adapter = strtolower($adapter);

        //当为memcache时
        if ($adapter == 'memcache') {
            return ($options) ? cache_memcache::getInstance($options) : doit::singleton('cache_memcache');
        }
        if (in_array($adapter, array('php', 'db', 'apc', 'xcache', 'eaccelerator', 'file'))) {
            return Controller::instance('cache_' . $adapter);
        }

        return false;
    }
}