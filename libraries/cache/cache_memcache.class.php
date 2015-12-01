<?php
/**
 * cache_memcache class file
 *
 * 注:部分代码参考了Qeephp 2.1 memecached.php代码
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: cache_memcache.class.php 1.3 2011-11-13 21:29:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

/**
 * 使用说明
 *
 * @author tommy<streen003@gmail.com>
 *
 * @example
 *
 * 参数范例
 * $mem_options = array(
 *     'servers'=> array(
 *         array('host'=>'127.0.0.1', 'port'=>11211, 'persistent'=>true, 'weight'=>1, 'timeout'=>60),
 *         array('host'=>'192.168.0.101', 'port'=>11211, 'persistent'=>true, 'weight'=>2, 'timeout'=>60),
 *     ),
 *     'compressed'=>true,
 *     'lifeTime' => 3600,
 *     'persistent' => true,
 * );
 *
 * 或在config项目目录中配置文件:memcache.ini.php,内容如下:
 * <?php
 * if(!defined('IN_DOIT')) exit();
 *
 * return array(
 *     'servers'=> array(
 *         array('host'=>'127.0.0.1', 'port'=>11211, 'persistent'=>true, 'weight'=>1, 'timeout'=>60),
 *         array('host'=>'192.168.0.101', 'port'=>11211, 'persistent'=>true, 'weight'=>2, 'timeout'=>60),
 *     ),
 *     'compressed'=>true,
 *     'lifeTime' => 3600,
 *     'persistent' => true,
 * );
 *
 *
 * 实例化
 *
 * 法一:
 * $memcache = new cache_memcache($mem_options);
 *
 */

class cache_memcache extends Base {

    /**
     * 单例模式实现化对象
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * Memcached实例
     *
     * @var objeact
     * @access private
     */
    private $_connection;

    /**
     * 默认的缓存服务器
     *
     * @var array
     */
    protected $_defaultServer = array(
        /**
         * 缓存服务器地址或主机名
         */
        'host' => '127.0.0.1',

        /**
         * 缓存服务器端口
         */
        'port' => '11211',
    );

    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_defaultOptions = array(

        /**
         * 缓存服务器配置,参看$_defaultServer
         * 允许多个缓存服务器
         */
        'servers' => array(),

        /**
         * 是否压缩缓存数据
         */
        'compressed' => false,

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'lifeTime' => 900,

        /**
         * 是否使用持久连接
         */
        'persistent' => true,
    );

    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct(array $options = null) {

        //分析memcache扩展模块的加载
        if (!extension_loaded('memcache')) {
            Controller::halt('The memcache extension must be loaded before use!');
        }

        //当参数为空时,程序则自动加载config目录中的memcache.ini.php的配置文件
        if (is_null($options)) {
            if (is_file(CONFIG_DIR . 'memcache.ini.php')) {
                $options = Controller::getConfig('memcache');
            }
        }

        if (is_array($options)) {
            $this->_defaultOptions = array_merge($this->_defaultOptions, $options);
        }

        if (empty($this->_defaultOptions['servers'])) {
            $this->_defaultOptions['servers'][] = $this->_defaultServer;
        }

        $this->_connection = new Memcache();

        foreach ($this->_defaultOptions['servers'] as $server) {
            $result = $this->_connection->addServer($server['host'], $server['port'], $this->_defaultOptions['persistent']);
            if (!$result) {
                Controller::halt(sprintf('Connect memcached server [%s:%s] failed!', $server['host'], $server['port']));
            }
        }

        return true;
    }

    /**
     * 写入缓存
     *
     * @param string $key    缓存Key
     * @param mixed $data    缓存内容
     * @param int $expire    缓存时间(秒)
     * @return void
     */
    public function set($id, $data, $expire = null) {

        if (is_null($expire)) {
            $expire = $this->_defaultOptions['lifeTime'];
        }

        $this->_connection->set($id, $data, empty($this->_defaultOptions['compressed']) ? 0 : MEMCACHE_COMPRESSED, $expire);
    }

    /**
     * 读取缓存,失败或缓存撒失效时返回false
     *
     * @param string $id
     * @return mixed
     */
    public function get($id) {

        return $this->_connection->get($id);
    }

    /**
     * 删除指定的缓存
     *
     * @param string $id
     * @return boolean
     */
    public function delete($id) {

        return $this->_connection->delete($id);
    }

    /**
     * 添加一条数据
     *
     * @author ColaPHP
     * @param string $key
     * @param int $value
     */
    public function add($key, $value = 1) {

        $this->_connection->increment($key, $value);
    }

    /**
     * 清除所有的缓存数据
     *
     * @return boolean
     */
     public function clear() {

          $this->_connection->flush();
     }

     /**
      * 获取memcache server状态
      *
      * @access public
      * @return string
      */
    public function stats() {

        return $this->_connection->getStats();
     }

     /**
      * 析构函数
      *
      * @access public
      * @return void
      */
     public function __destruct() {

         if ($this->_connection) {
             $this->_connection->close();
         }
     }

    /**
     * 单例模式实例化本类
     *
     * @access public
     * @return object
     */
    public static function getInstance($options = null) {

        if (self::$_instance === null) {
            self::$_instance = new cache_memcache($options);
        }

        return self::$_instance;
    }
}