<?php
/**
 * queue class file
 *
 * 队列操作
 * @author DaBing<InitPHP>, tommy
 * @copyright  CopyRight DoitPHP team, initphp team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: queue.class.php 1.3 2011-11-13 21:17:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class queue extends Base {

    /**
     * 存放队列数据
     *
     * @var array
     */
    private static $queue = array();

    /**
     * 队列-设置值
     *
     * @param string    $value    加入队列的值
     * @return string
     */
    public static function set($value) {

        array_unshift(self::$queue, $value);
        return true;
    }

    /**
     * 队列-从队列中获取一个最早放进队列的值
     *
     * @return string
     */
    public static function get() {

        return array_pop(self::$queue);
    }

    /**
     * 队列-队列中总共有多少值
     *
     * @return string
     */
    public function count() {

        return count(self::$queue);
    }

    /**
     * 队列-清空队列数据
     *
     * @return string
     */
    public function clear() {

        self::$queue = array();
    }
}