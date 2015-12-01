<?php
/**
 * pincode.class.php
 *
 * 生成、显示、验证码
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: pincode.class.php 1.3 2011-11-13 21:12:00Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class pincode extends Base {

    /**
     * 验证码图片的背景图片.
     *
     * @var string
     */
    public $imageUrl;

    /**
     * 字体名称
     *
     * @var sting
     */
    public $fontName;

    /**
     * 字体大小
     *
     * @var integer
     */
    public $fontSize;

    /**
     * 图片实例化名称
     *
     * @var object
     */
    protected $image;

    /**
     * 图象宽度
     *
     * @var integer
     */
    protected $width;

    /**
     * 图象高度
     *
     * @var integer
     */
    protected $height;

    /**
     * 图片格式, 如:jpeg, gif, png
     *
     * @var string
     */
    protected $type;

    /**
     * 字体颜色
     *
     * @var string
     */
    protected $fontColor;

    /**
     * 背景的颜色
     *
     * @var string
     */
    protected $bgColor;

    /**
     * 验证码内容
     *
     * @var string
     */
    protected $textContent;

    /**
     * 生成验证码SESSION的名称,用于类外数据验证
     *
     * @var string
     */
    public $sessionName;


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->fontSize     = 14;
        $this->fontName     = DOIT_ROOT . 'view/source/aispec.ttf';
        $this->sessionName  = 'xplusplus_pincode_session_id';
        $this->width        = 90;
        $this->height       = 30;

        return true;
    }

    /**
     * 设置字体名称.
     *
     * @param sting $name    字体名称(字体的路径)
     * @param integer $size    字体大小
     */
    public function setFontName($name, $size = null) {

        if (!empty($name)) {
            $this->fontName = $name;
        }
        if (!is_null($size)) {
            $this->fontSize = intval($size);
        }

        return $this;
    }

    /**
     * 设置字体大小.
     *
     * @param integer $size    字体大小
     * @return $this
     */
    public function setFontSize($size) {

        if (!empty($size)) {
            $this->fontSize = intval($size);
        }

        return $this;
    }

    /**
     * 设置背景图片或水印图片的URL.
     *
     * @param string $url    图片的路径(图片的实际地址)
     * @return $this
     */
    public function setBgImage($url) {

        if (!empty($url)) {
            $this->imageUrl = $url;
        }

        return $this;
    }

    /**
     * 设置生成图片的大小.
     *
     * @param integer $width    图片的宽度
     * @param integer $height    图片的高度
     * @return $this
     */
    public function setImageSize($width, $height) {

        if (!empty($width)) {
            $this->width  = (int)$width;
        }
        if (!empty($height)) {
            $this->height = (int)$height;
        }

        return $this;
    }

    /**
     * 设置验证码的sessionName.
     *
     * @param string $name
     */
    public function setSessionName($name) {

        if (!empty($name)) {
            $this->sessionName = $name;
        }

        return $this;
    }

    /**
     * 设置验证码内容.
     *
     * @param string $content
     * @return $this
     */
    public function setTextContent($content) {

        if (!empty($content)) {
            $this->textContent = $content;
        }

        return $this;
    }

    /**
     * 获取颜色参数.
     *
     * @param string $param    颜色参数. 如：#FF0000
     * @return $this
     */
    public function setTextColor($param) {

        //将十六进制颜色值转化为十进制
        $x = hexdec(substr($param, 1, 2));
        $y = hexdec(substr($param, 3, 2));
        $z = hexdec(substr($param, 5, 2));

        $this->fontColor = array($x, $y, $z);

        return $this;
    }

    /**
     * 获取背景的颜色参数
     *
     * @param string $param    颜色参数. 如：#FF0000
     * @return $this
     */
    public function setBgColor($param) {

        //将十六进制颜色值转化为十进制
        $x = hexdec(substr($param, 1, 2));
        $y = hexdec(substr($param, 3, 2));
        $z = hexdec(substr($param, 5, 2));

        $this->bgColor = array($x, $y, $z);

        return $this;
    }

    /**
     * 生成验证码内容.
     *
     * @return stirng
     */
    protected function getPincodeContent() {

        if (!$this->textContent) {
            $char = 'BCEFGHJKMPQRTVWXY2346789';
            $num1 = $char[mt_rand(0, 23)];
            $num2 = $char[mt_rand(0, 23)];
            $num3 = $char[mt_rand(0, 23)];
            $num4 = $char[mt_rand(0, 23)];
            $this->textContent = $num1 . $num2 . $num3 . $num4;
        }

        return $this->textContent;
    }

    /**
     * 验证码的判断
     *
     * @access public
     * @param string $code    待验证的验证码内容
     * @return boolean
     */
    public function check($code) {

        if (!$code) {
            return false;
        }
        $code = strtolower($code);

        //start session
        session_start();

        if(isset($_SESSION[$this->sessionName]) && (strtolower($_SESSION[$this->sessionName]) == $code)){
        	//invalidate pincode
        	unset($_SESSION[$this->sessionName]);
        	return true;
        }else return false;
    }

    /**
     * 显示验证码.
     *
     * @access public
     * @param string $imageUrl    验证码的背影图片路径
     * @return void
     */
    public function show($imageUrl = null) {

        //当前面没有session_start()调用时.
        session_start();

        if (!is_null($imageUrl)) {
            $this->imageUrl = trim($imageUrl);
        }

        $this->image = (!function_exists('imagecreatetruecolor')) ? imagecreate($this->width, $this->height) : imagecreatetruecolor($this->width, $this->height);

        //当有背景图片存在时
        if ($this->imageUrl) {

            //初始化图片信息.
            list($imageWidth, $imageHeight, $type) = getimagesize($this->imageUrl);

            //分析图片的格式
            switch ($type) {
                case 1:
                    $image          = imagecreatefromgif ($this->imageUrl);
                    $this->type     = 'gif';
                    break;

                case 2:
                    $image          = imagecreatefromjpeg($this->imageUrl);
                    $this->type     = 'jpg';
                    break;

                case 3:
                    $image          = imagecreatefrompng($this->imageUrl);
                    $this->type     = 'png';
                    break;

                case 4:
                    $image          = imagecreatefromwbmp($this->imageUrl);
                    $this->type     = 'bmp';
                    break;
            }

            //背景
            $srcX = ($imageWidth > $this->width) ? mt_rand(0, $imageWidth - $this->width) : 0;
            $srcY = ($imageHeight > $this->height) ? mt_rand(0, $imageHeight - $this->height) : 0;
            imagecopymerge($this->image, $image, 0, 0, $srcX, $srcY, $this->width, $this->height, 100);
            imagedestroy($image);

            //边框
            $borderColor   = imagecolorallocate($this->image, 255, 255, 255);
            imagerectangle($this->image, 1, 1, $this->width - 2, $this->height - 2, $borderColor);

        } else {

            //定义图片类型
            $this->type     = 'png';

            //背景
            $bgColorArray   = (!$this->bgColor) ? array(255, 255, 255) : $this->bgColor;
            $back_color     = imagecolorallocate($this->image, $bgColorArray[0], $bgColorArray[1], $bgColorArray[2]);
            imagefilledrectangle($this->image, 0, 0, $this->width -1, $this->height - 1, $back_color);

            //边框
            $borderColor    = imagecolorallocate($this->image, 238, 238, 238);
            imagerectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $borderColor);
        }

        //获取验证码内容.
        $this->getPincodeContent();

        //验证码中含有汉字
        if (!preg_match('~[\x{4e00}-\x{9fa5}]+~u', $this->textContent)) {
            //计算验证码的位数
            $codeStrlen    = strlen($this->textContent);
            //每位验证码所占用的图片宽度
            $perWidth      = ceil(($this->width - 10)/ $codeStrlen);

            for($i = 0; $i < $codeStrlen; $i ++) {

                //获取单个字符
                $textContent = $this->textContent[$i];

                $bbox        = imagettfbbox($this->fontSize, 0, $this->fontName, $textContent);
                $fontW       = $bbox[2]-$bbox[0];
                $fontH       = abs($bbox[7]-$bbox[1]);

                $fontX       = ceil(($perWidth - $fontW) / 2) + $perWidth * $i + 5;
                $min_y       = $fontH + 5;
                $max_y       = $this->height -5;
                $fontY       = rand($min_y, $max_y);

                $fontColor   = (!$this->fontColor) ? imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->image, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
                imagettftext($this->image, $this->fontSize, 0, $fontX, $fontY, $fontColor, $this->fontName, $textContent);
            }
        } else {
            //分析验证码的位置
            $bbox            = imagettfbbox($this->fontSize, 0, $this->fontName, $this->textContent);
            $fontW           = $bbox[2]-$bbox[0];
            $fontH           = abs($bbox[7]-$bbox[1]);
            $fontX           = ceil(($this->width - $fontW) / 2) + 5;
            $min_y           = $fontH + 5;
            $max_y           = $this->height -5;
            $fontY           = rand($min_y, $max_y);

            $fontColor       = (!$this->fontColor) ? imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->image, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
            imagettftext($this->image, $this->fontSize, 0, $fontX, $fontY, $fontColor, $this->fontName, $this->textContent);
        }

        //干扰线
        for ($i = 0; $i < 5; $i ++) {
            $line_color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imageline($this->image, mt_rand(2, $this->width - 3), mt_rand(2, $this->height  - 3), mt_rand(2, $this->width  - 3), mt_rand(2, $this->height  - 3), $line_color);
        }

        //将显示的验证码赋值给session.
        $_SESSION[$this->sessionName] = $this->textContent;

        //当有headers内容输出时.
        if (headers_sent()) {
            Controller::halt('headers already sent');
        }

        //显示图片,根据背景图片的格式显示相应格式的图片.
        switch ($this->type) {

            case 'gif':
                header('Content-type:image/gif');
                imagegif ($this->image);
                break;

            case 'jpg':
                header('Content-type:image/jpeg');
                imagejpeg($this->image);
                break;

            case 'png':
                header('Content-type:image/png');
                imagepng($this->image);
                break;

            case 'bmp':
                header('Content-type:image/wbmp');
                imagewbmp($this->image);
                break;
        }

        imagedestroy($this->image);
    }
}