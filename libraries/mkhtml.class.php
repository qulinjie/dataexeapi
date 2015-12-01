<?php
/**
 * mkhtml class file
 *
 * 生成HTML静态文件类
 * @author tommy
 * @copyright  CopyRight DoitPHP team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: mkhtml.class.php 1.3 2011-11-13 20:09:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class mkhtml extends Base {

    /**
     * 静态文件的存放目录
     *
     * @var string
     */
    protected $htmlPath;

    /**
     * HTML文件名生成规则
     *
     * @var array
     */
    protected $rule;

    /**
     * HTML重写功能开启状态
     *
     * @var boolean
     */
    protected $isWrite;

    /**
     * HTML文件名
     *
     * @var string
     */
    protected $fileName;

    /**
     * 构造函数
     *
     * @access publid
     * @return boolean
     */
    public function __construct() {

        $this->htmlPath     = CACHE_DIR . 'html/';
        $this->isWrite      = false;
        $this->makeDir($this->htmlPath);

        return true;
    }

    /**
     * 获取生成html文件名规则
     *
     * @access public
     * @params array $ruleArray    规则
     * @return $this
     */
    public function rule($ruleArray) {

        if ($ruleArray && is_array($ruleArray)) {
            $this->rule = $ruleArray;
        }

        return $this;
    }

    /**
     * 设置HTML存放目录
     *
     * @access public
     * @params string $path    HTML文件存路径
     * @return $this
     */
    public function setPath($path) {

        if ($path) {
            $this->htmlPath = $path;
        }

        return $this;
    }

    /**
     * 页面缓存开启
     *
     * @access public
     * @param string $fileName    文件名
     * @return void
     */
    public function start($fileName = null) {

        //parse file name
        $fileName = $this->getFileName($fileName);

        //分析重写开关
        if(is_file($fileName)) {
            include $fileName;
            exit();
        }

        $this->isWrite     = true;
        $this->fileName    = $fileName;

        ob_start();
    }

    /**
     * 页面缓存结束
     *
     * @access public
     * @return string
     */
    public function end() {

        //获取页面内容
        $htmlContent = ob_get_clean();

        if ($this->fileName && ($this->isWrite === true)) {

            //分析存放目录
            $this->makeDir(dirname($this->fileName));

            file_put_contents($this->fileName, $htmlContent, LOCK_EX);
        }

        echo $htmlContent;
    }

    /**
     * 获取生成的文件名
     *
     * @access protected
     * @param string $fileName    文件名
     * @return string
     */
    protected function getFileName($fileName = null) {

        //当参数为空时..自动匹配文件名
        if (is_null($fileName)) {
            if ($this->rule) {
                //当项目开启Rewrite设置时
                if (DOIT_REWRITE === false) {
                    $pathUrlString = strlen($_SERVER['SCRIPT_NAME']) > strlen($_SERVER['REQUEST_URI']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['REQUEST_URI'];
                    $pathUrlString = str_replace($_SERVER['SCRIPT_NAME'], '', $pathUrlString);
                } else {
                    $pathUrlString = str_replace(str_replace('/' . ENTRY_SCRIPT_NAME, '', $_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI']);
                    //去掉伪静态网址后缀
                    $pathUrlString = str_replace(URL_SUFFIX, '', $pathUrlString);
                }

                foreach($this->rule as $key=>$value) {
                    $key = str_replace(array(':any', ':num'), array('.+?', '[0-9]+'), $key);
                    if(preg_match('#' . $key . '#', $pathUrlString)) {
                        $pathUrlString = preg_replace('#' . $key . '#', $value, $pathUrlString);
                        break;
                    }
                }
                $fileName = basename($pathUrlString);
            } else {
                $fileName = doit::getActionName();
            }
        }

        return $this->htmlPath . doit::getControllerName() . '/' . $fileName . '.html';
    }

    /**
     * 将url页面内容写入html文件
     *
     * @access public
     * @param string $fileName    所要生成的HTML文件名(注：不带.html后缀)
     * @param string $url        所要生成HTML的页面的URL
     * @return boolean
     */
    public function createHtml($fileName, $url) {

        //parse params
        if (!$fileName || !$url) {
            return false;
        }

        $fileName = $this->htmlPath . $fileName . '.html';

        //当存放html的目录不存在时
        $this->makeDir(dirname($fileName));

        ob_start();
        //获取并显示url的页面内容
        echo file_get_contents($url);

        return file_put_contents($fileName, ob_get_clean(), LOCK_EX);
    }

    /**
     * 创建目录
     *
     * @access protected
     * @param string $dirName    所要创建目录的路径
     * @return void
     */
    protected function makeDir($dirName) {

        if (!$dirName) {
            return false;
        }

        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        } else if (!is_writeable($dirName)) {
            chmod($dirName, 0777);
        }
    }
}