<?php
/**
 * db_redis.class.php
 *
 * 用于redis数据库的操作(no sql)
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: db_redis.class.php 1.4 2012-08-17 23:15:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class db_redis extends Base {

    /**
     * 单例模式实例化对象
     *
     * @var object
     */
    protected static $_instance;

    /**
     * 数据库连接ID
     *
     * @var object
     */
    protected $dbLink;

    /**
     * 构造函数
     *
     * @access public
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     * @return boolean
     */
    public function __construct(array $options = null) {

        if (!extension_loaded('redis')) {
            Controller::halt('The redis extension must be loaded!');
        }

        //当参数为空时,程序则自动加载config目录中的redis.ini.php的配置文件
        if (is_null($options)) {
            if (is_file(CONFIG_DIR . 'redis.ini.php')) {
                $options = Controller::getConfig('redis');
            }
        }

        $options['host'] = (!$options['host']) ? '127.0.0.1' : $options['host'];
        $options['port'] = (!$options['port']) ? '6379' : $options['port'];

        //连接数据库
        $this->dbLink  = new Redis();
        $this->dbLink->connect($options['host'], $options['port']);

        return true;
    }

    /**
     * 设置数据值
     *
     * @access public
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param int $timeOut 时间
     * @return boolean
     */
    public function set($key, $value, $timeOut = 0) {

        $value  = json_encode($value);
        $result = $this->dbLink->set($key, $value);
        if ($timeOut > 0) {
            $this->dbLink->setTimeout($key, $timeOut);
        }

        return $result;
    }

    /**
     * 通过KEY获取数据
     *
     * @access public
     * @param string $key KEY名称
     * @return string | array
     */
    public function get($key) {

        $value = $this->dbLink->get($key);
        return json_decode($value, true);
    }

    /**
     * 删除一条数据
     *
     * @access public
     * @param string $key KEY名称
     * @return boolean
     */
    public function delete($key) {

        return $this->dbLink->delete($key);
    }

    /**
     * 清空数据
     *
     * @access public
     * @return boolean
     */
    public function flushAll() {

        return $this->dbLink->flushAll();
    }

    /**
     * 数据入队列
     *
     * @access public
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     * @return boolean
     */
    public function push($key, $value,$right = true) {

        $value = json_encode($value);
        return ($right == true) ? $this->dbLink->rPush($key, $value) : $this->dbLink->lPush($key, $value);
    }

    /**
     * 数据出队列
     *
     * @access public
     * @param string $key KEY名称
     * @param bool $left 是否从左边开始出数据
     * @return string | string
     */
    public function pop($key, $left = true) {

        $value = ($left == true) ? $this->dbLink->lPop($key) : $this->dbLink->rPop($key);
        return json_decode($value);
    }

    /**
     * 数据自增
     *
     * @access public
     * @param string $key KEY名称
     * @return boolean
     */
    public function incr($key) {

        return $this->dbLink->incr($key);
    }

    /**
     * 数据自减
     *
     * @access public
     * @param string $key KEY名称
     * @return boolean
     */
    public function decr($key) {

        return $this->dbLink->decr($key);
    }

    /**
     * key是否存在，存在返回ture
     *
     * @access public
     * @param string $key KEY名称
     * @return boolean
     */
    public function exists($key) {

        return $this->dbLink->exists($key);
    }

    /**
     * 返回redis对象
     *
     * @access public
     * @return object
     */
    public function redis() {

        return $this->dbLink;
    }

    /**
     * 单例模式
     *
     * @access public
     * @param array $params 数据库连接参数
     * @return object
     */
    public static function getInstance($params = null) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

}