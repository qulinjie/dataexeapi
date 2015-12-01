<?php
/**
 * cache_file class file
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: cache_file.class.php 1.3 2011-11-13 21:27:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class cache_file extends Base {

    /**
     * 缓存目录
     *
     * @var string
     */
     public $cacheDir;


     /**
      * 构造函数,初始化变量
      *
      * @access public
      * @return boolean
      */
     public function __construct() {

         //设置缓存目录
         $this->cacheDir = CACHE_DIR . 'data' . DIRECTORY_SEPARATOR;

         return true;
     }

    /**
     * 分析缓存文件名.
     *
     * @param string $fileName
     * @return string
     */
    protected function parseCacheFile($fileName) {

        return $this->cacheDir . md5($fileName) .'.cache.tmp';
    }

    /**
     * 设置缓存
     *
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function set($key, $value) {

        //参数分析
        if (!$key) {
            return false;
        }

        //分析缓存文件
        $cacheFile = $this->parseCacheFile($key);
        //分析缓存内容
        $value     = (!is_array($value)) ? serialize(trim($value)) : serialize($value);

        //分析缓存目录
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        } else if (!is_writeable($this->cacheDir)) {
            chmod($this->cacheDir, 0777);
        }

        return file_put_contents($cacheFile, $value, LOCK_EX) ? true : false;
    }

    /**
     * 获取一个已经缓存的变量
     *
     * @param string $key
     * @return string
     */
    public function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        //分析缓存文件
        $cacheFile = $this->parseCacheFile($key);

        return is_file($cacheFile) ? unserialize(file_get_contents($cacheFile)) : false;
    }

    /**
     * 删除缓存
     *
     * @param string $key
     * @return void
     */
    public function delete($key) {

        //参数分析
        if (!$key) {
            return true;
        }

        //分析缓存文件
        $cacheFile = $this->parseCacheFile($key);

        return is_file($cacheFile) ? unlink($cacheFile) : true;
    }
}