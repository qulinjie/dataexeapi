<?php
/**
 * cache_db class file
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: cache_db.class.php 1.3 2011-11-13 21:25:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class cache_db extends Base {

    /**
     * 缓存目录
     *
     * @var string
     */
     public $cacheDir;

     /**
      * 缓存有效周期
      *
      * @var integer
      */
     public $lifetime;


     /**
      * 构造函数,初始化变量
      *
      * @access public
      * @return boolean
      */
     public function __construct() {

         //设置缓存目录
         $this->cacheDir = CACHE_DIR . 'data' . DIRECTORY_SEPARATOR;
         //默认缓存周期为24小时
         $this->lifetime = 86400;

         return true;
     }

    /**
     * 分析缓存文件的路径.
     *
     * @param string $fileName
     * @return string
     */
    protected function parseCacheFile($fileName) {

        return $this->cacheDir . $fileName . '_cache.db.php';
    }

    /**
     * 设置缓存周期.
     *
     * @param integer $lifeTime
     * @return $this
     */
    public function lifetime($lifeTime) {

        if (!$lifeTime) {
            return false;
        }

        $this->lifetime = (int)$lifeTime;

        return $this;
    }

    /**
     * 缓存分析,判断是否开启缓存重写开关
     *
     * @param string $cacheFile
     * @return boolean
     */
    protected function parseCache($cacheFile) {

        if (!is_file($cacheFile)) {
            return true;
        }

        return ($_SERVER['REQUEST_TIME'] - filemtime($cacheFile) > $this->lifetime) ? true : false;
    }

    /**
     * 创建缓存文件
     *
     * @param string $cacheFile
     * @param array  $cacheContent
     * @return void
     */
    protected function createCache($cacheFile, $cacheContent) {

        //分析缓存文件内容
        $contents = "<?php\r\nif (!defined('IN_DOIT')) exit();\r\nreturn ";
        $contents .= var_export($cacheContent, true) . ';';

        //当缓存目录不存在时,自行创建目录。
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        } else if (!is_writeable($this->cacheDir)) {
            chmod($this->cacheDir, 0777);
        }

        //将缓存内容写入文件
        file_put_contents($cacheFile, $contents, LOCK_EX);
    }

    /**
     * 加载缓存文件内容
     *
     * 本类中的主函数,当第二参数为空时,则默认数据表内全部数据表字段的数据
     * @param string $tableName
     * @param array  $filter
     * @return array
     */
    public function load($tableName, $filter = array()) {

        //参数分析
        if (empty($tableName)) {
            return false;
        }

        //分析缓存文件名
        $cacheFile   = $this->parseCacheFile($tableName);

        //缓存文件内容需要更新
        if ($this->parseCache($cacheFile)) {
            //获取数据表内容
            $model   = Controller::model($tableName);
            $data    = $model->findAll();

            //分析当有数据表字段过滤时
            $cacheContent = array();
            //当数据表有数据时
            if ($data) {
                 if ($filter && is_array($filter)) {
                    foreach ($data as $key=>$value) {
                        foreach ($filter as $columnName) {
                            $cacheContent[$key][$columnName] = $value[$columnName];
                        }
                    }
                } else {
                    $cacheContent = $data;
                }
            }

            //清空不必要的内容占用
            unset($data);

            //生成缓存文件
            $this->createCache($cacheFile, $cacheContent);

            return $cacheContent;
        }

        return include $cacheFile;
    }

    /**
     * 加载设置信息缓存文件内容
     *
     * @access public
     * @param string $tableName
     * @param string $key
     * @param string $value
     * @return array
     */
    public function loadConfig($tableName, $key, $value) {

        //参数分析
        if (!$tableName || !$key || !$value) {
            return false;
        }

        //分析缓存文件名
        $cacheFile    = $this->parseCacheFile($tableName);

        if ($this->parseCache($cacheFile)) {
            //获取数据表内容
            $model   = Controller::model($tableName);
            $data    = $model->findAll();

            //分析当有数据表字段过滤时
            $cacheContent = array();
            //当数据表有数据时
            if ($data) {
                foreach ($data as $lines) {
                    $cacheContent[$lines[$key]] = $lines[$value];
                }
            }

            //清空不必要的内容占用
            unset($data);

            //生成缓存文件
            $this->createCache($cacheFile, $cacheContent);

            return $cacheContent;
        }

        return include $cacheFile;
    }

    /**
     * 删除缓存文件
     *
     * @param string $fileName
     * @return boolean
     */
    public function delete($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        //分析缓存文件名
        $cacheFile     = $this->parseCacheFile($fileName);

        return is_file($cacheFile) ? unlink($cacheFile) : true;
    }
}