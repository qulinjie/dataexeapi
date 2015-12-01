<?php
/**
 * upload class file
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: upload.class.php 1.3 2011-11-13 20:58:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class upload extends Base {

    /**
     * 文件大小
     *
     * @var integer
     */
    protected $limitSize;

    /**
     * 文件名字
     *
     * @var string
     */
    protected $fileName;

    /**
     * 文件类型
     *
     * @var string
     */
    protected $limitType;


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function  __construct() {

        $this->limitSize = 2097152;    //默认文件大小 2M
        return true;
    }

    /**
     * 初始化运行环境
     *
     * @param string $file
     * @return boolean
     */
    protected function parseInit($file) {

        $this->fileName = $file;

        if ($this->fileName['size'] > $this->limitSize) {
            echo '文件:'.$this->fileName['name'].' 大小超出上传限制!';
            exit();
        }
        if ($this->limitType) {
            $this->parseMimetype($file);
        }

        return true;
    }

    /**
     * 设置上传文件的大小限制.
     *
     * @param integer $size
     * @return unkonow
     */
    public function setLimitSize($size) {

        if ($size) {
            $this->limitSize = $size;
        }

        return $this;
    }

    /**
     * 设置上传文件允许的格式
     *
     * @param string $type
     * @return unkonow
     */
    public function setLimitType($type) {

        if (!$type || !is_array($type)) {
            return false;
        }

        $this->limitType = $type;
        return $this;
    }

    /**
     * 验证上传文件的格式
     *
     * @return boolean
     */
    protected function parseMimetype() {

        //上传文件允许的格式
        $mimeType = array(
        'jpg'=>'image/jpeg',
        'gif'=>'image/gif',
        'png'=>'image/png',
        'bmp'=>'image/bmp',
        'html'=>'text/html',
        'css'=>'text/css',
        'wbmp'=>'image/vnd.wap.wbmp',
        'js'=>'application/x-javascript',
        'swf'=>'application/x-shockwave-flash',
        'xml'=>'application/xhtml+xml',
        'php'=>'application/x-httpd-php',
        'txt'=>'text/plain',
        'wma'=>'audio/x-ms-wma',
        'mp3'=>'audio/mpeg',
        'zip'=>'application/zip',
        'rar'=>'application/x-rar-compressed',
        'flv'=>'flv-application/octet-stream',
        );

        //判断limitType是否在允许上传文件格式列表之内
        $mimeTypeKey = array_keys($mimeType);

        foreach($this->limitType as $type) {
            if (!in_array($type, $mimeTypeKey)) {
                echo '文件格式不在允许上传的范围之内!';
                exit();
            }
        }

        $allowTypeArray = array();
        foreach($this->limitType as $type) {
            $allowTypeArray[] = $mimeType[$type];
        }

        if (!in_array($this->fileName['type'], $allowTypeArray)) {
            echo '上传失败:你上传的文件格式不正确!';
            exit();
        }

        return true;
    }

    /**
     * 上传文件
     *
     * @param string $fileUpload    文件名字
     * @param string $fileName        上传后的目标文件名
     * @return boolean
     */
    public function upload($fileUpload, $fileName) {

        //参数分析
        if (!is_array($fileUpload) || empty($fileName)) {
            return false;
        }

        $this->parseInit($fileUpload);

        if (!move_uploaded_file($this->fileName['tmp_name'], $fileName)) {
            return false;
        }

        return true;
    }
}