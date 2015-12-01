<?php
/**
 * lang.class.php
 *
 * 项目多语言管理
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: lang.class.php 1.0 2011-12-24 20:38:04Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class language extends Base {

    /**
     * 语言包的目录
     *
     * @var string
     */
    protected $_basePath;

    /**
     * 语言包名称
     *
     * @var string
     */
    protected $_languageName;

    /**
     * 语言文件的后缀
     *
     * @var string
     */
    protected $_suffix;

    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {
        $this->_basePath     = LANG_DIR;
        $this->_languageName = 'zh_cn';
        $this->_suffix       = '.php';

        return true;
    }

    /**
     * 设置语言名称
     *
     * @access public
     * @param string $languageName 语言名称
     * @return $this
     */
    public function setLanguage($languageName) {

        //参数分析
        if (!$languageName) {
            return false;
        }

        $this->_languageName = $languageName;

        return $this;
    }

    /**
     * 获取语言名称
     *
     * @access public
     * @return string
     */
    public function getLanguage() {

        return $this->_languageName;
    }

    /**
     * 设置语言包的基本路径
     *
     * @access public
     * @param string $path 语言包的基本路径
     * @return $this
     */
    public function setBasePath($path) {

        //参数分析
        if (!$path) {
            return false;
        }

        $this->_basePath = $path;

        return $this;
    }

	/**
     * 获取语言包的基本路径
     *
     * @access public
     * @return string
     */
    public function getBasePath() {

        return $this->_basePath;
    }

    /**
     * 设置语言文件的后缀
     *
     * @access public
     * @param string $suffix 语言文件的后缀名称
     * @return $this
     */
    public function setSuffix($suffix) {

        //参数分析
        if (!$suffix) {
            return false;
        }

        $this->_suffix = $suffix;

        return $this;
    }

    /**
     * 获取语言文件的后缀
     *
     * @access public
     * @return string
     */
    public function getSuffix() {

        return $this->_suffix;
    }

    /**
     * 加载语言数据文件
     *
     * @access public
     * @param string $fileName 语言数据文件名称
     * @return array
     */
    public function getFile($fileName = null) {

        //分析语言文件的路径
        $languageFile  = $this->_basePath . $this->_languageName;
        $languageFile .= (!is_null($fileName) ? '/' . $fileName : '') . $this->_suffix;

        static $_loadArray = array();
        if ($_loadArray[$languageFile] == null) {
            //判断文件是否存在
            if (!is_file($languageFile)) {
                Controller::halt('The File:' . $languageFile . ' is not exists');
            }
            $_loadArray[$languageFile] = include_once $languageFile;
        }

        return $_loadArray[$languageFile];
    }

    /**
     * 获取语言包某键值的内容
     *
     * @access public
     * @param string $key 键值
     * @param string $fileName 语言数据文件名称
     * @return string
     */
    public function get($key, $fileName = null) {

        $langArray = $this->getFile($fileName);

        return isset($langArray[$key]) ? $langArray[$key] : $key;
    }
}