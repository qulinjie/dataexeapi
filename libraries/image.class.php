<?php
/**
 * image class file
 *
 * 用于处理图片常用操作,如:生成缩略图,图片水印生成等
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: image.class.php 1.3 2010-11-13 21:06:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

if (!function_exists('getimagesizefromstring')) {
	function getimagesizefromstring($string_data)
	{
		$uri = 'data://application/octet-stream;base64,'  . base64_encode($string_data);
		return getimagesize($uri);
	}
}

class image extends Base {

    /**
     * 原图片路径,该图片在验证码时指背景图片,在水印图片时指水印图片.
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
     * 文字的横坐标
     *
     * @var integer
     */
    public $fontX;

    /**
     * 文字的纵坐标
     *
     * @var integer
     */
    public $fontY;

    /**
     * 字体颜色
     *
     * @var array
     */
    protected $fontColor;

    /**
     * 字体阴影
     * @var array
     */
    protected $fontBgColor;
    
    /**
     * 生成水印图片的原始图片的宽度
     *
     * @var integer
     */
    protected $imageWidth;

    /**
     * 生成水印图片的原始图片的高度
     *
     * @var integer
     */
    protected $imageHeight;

    /**
     * 生成缩略图的实际宽度
     *
     * @var integer
     */
    protected $widthNew;

    /**
     * 生成缩略图的实际高度
     *
     * @var integer
     */
    protected $heightNew;

    /**
     * 水印图片的实例化对象
     *
     * @var object
     */
    protected $waterImage;

    /**
     * 生成水印区域的横坐标
     *
     * @var integer
     */
    protected $waterX;

    /**
     * 生成水印区域的纵坐标
     *
     * @var integer
     */
    protected $waterY;

    /**
     * 生成水印图片的水印区域的透明度
     *
     * @var integer
     */
    protected $alpha;

    /**
     * 文字水印字符内容
     *
     * @var string
     */
    protected $textContent;

    /**
     * 水印图片的宽度
     *
     * @var integer
     */
    protected $waterWidth;

    /**
     * 水印图片的高度
     *
     * @var integer
     */
    protected $waterHeight;


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->fontSize = 14;
        $this->fontName = DOIT_ROOT . 'view/source/aispec.ttf';

        return true;
    }

    /**
     * 初始化运行环境,获取图片格式并实例化.
     *
     * @param string $data 图片内容数据
     * @return boolean
     */
    protected function parseImageInfoFromString($data){
    	$ret = getimagesizefromstring($data);
    	if(false === $ret) return false;
    	list($this->imageWidth, $this->imageHeight, $type) = $ret;
    	$this->image = imagecreatefromstring($data);
    	if($this->image === false) return false;
    	switch ($type) {
    		case 1:
    			$this->type  = 'gif';
    			break;
    		case 2:
    			$this->type  = 'jpg';
    			break;
    		case 3:
    			//bug fix:透明背景的png需要使用imagesavealpha保存alpha通道才能不变成背景黑色
    			imagesavealpha($this->image, true);
    			$this->type  = 'png';
    			break;
    		case 4:
    			$this->type  = 'bmp';
    			break;
    	}
    	return true;
    }
    
    /**
     * 初始化运行环境,获取图片格式并实例化.
     *
     * @param string $url 图片路径
     * @return boolean
     */
    protected function parseImageInfo($url) {

        $ret = getimagesize($url);
		if(false === $ret) {
			return false;
		}
		list($this->imageWidth, $this->imageHeight, $type) = $ret;
        switch ($type) {
            case 1:
                $this->image = imagecreatefromgif ($url);
                $this->type  = 'gif';
                break;

            case 2:
                $this->image = imagecreatefromjpeg($url);
                $this->type  = 'jpg';
                break;

            case 3:
                $this->image = imagecreatefrompng($url);
                //bug fix:透明背景的png需要使用imagesavealpha保存alpha通道才能不变成背景黑色
                imagesavealpha($this->image, true);
                $this->type  = 'png';
                break;

            case 4:
                $this->image = imagecreatefromwbmp($url);
                $this->type  = 'bmp';
                break;
        }

        if(false === $this->image) {
        	return false;
        }
        
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
            $this->fontSize = (int)$size;
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
     * 获取颜色参数.
     *
     * @param integer $x    RGB色彩中的R的数值
     * @param integer $y    RGB色彩中的G的数值
     * @param integer $z    RGB色彩中的B的数值
     * @return $this
     */
    public function setFontColor($x=false, $y=false, $z=false) {

        $this->fontColor = (is_int($x) && is_int($y) && is_int($z)) ? array($x, $y, $z) : array(255, 255, 255);

        return $this;
    }
    
    /**
     * 设置字体背景色
     */
    public function setFontBgColor($x = false, $y = false, $z = false){
    	$this->fontBgColor = (is_int($x) && is_int($y) && is_int($z)) ? array($x, $y, $z) : array(128,128,128);
    	return $this;
    }

    /**
     * 水印图片的URL.
     *
     * @param string $url    图片的路径(图片的实际地址)
     * @return $this
     */
    public function setImageUrl($url) {

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
            $this->width = (int)$width;
        }
        if (!empty($height)) {
            $this->height = (int)$height;
        }

        return $this;
    }

    /**
     * 设置文字水印字符串内容.
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

    public function getTextContent(){
    	return $this->textContent;
    }
    
    /**
     * 设置文字水印图片文字的坐标位置.
     *
     * @param integer $x    水印区域的横坐标
     * @param integer $y    水印区域的纵坐标
     * @return $this
     */
    public function setTextPosition($x, $y) {

        if (!empty($x)) {
            $this->fontX = (int)$x;
        }
        if (!empty($y)) {
            $this->fontY = (int)$y;
        }

        return $this;
    }


    /**
     * 设置水印图片水印的坐标位置.
     *
     * @param integer $x    水印区域的横坐标
     * @param integer $y    水印区域的纵坐标
     * @return $this
     */
    public function setWatermarkPosition($x, $y) {

        if (!empty($x)) {
            $this->waterX = (int)$x;
        }
        if (!empty($y)) {
            $this->waterY = (int)$y;
        }

        return $this;
    }

    /**
     * 设置水印图片水印区域的透明度.
     *
     * @param integer $param    水印区域的透明度
     * @return $this
     */
    public function setWatermarkAlpha($param) {

        if (!empty($param)) {
            $this->alpha = intval($param);
        }

        return $this;
    }

    /**
     * 调整文字水印区域的位置
     *
     * @return boolean
     */
    protected function handleWatermarkFontPlace($limitOption = false) {

        if (!$this->fontX || !$this->fontY) {
            if (!$this->textContent) {
                return false;
            }

            $bbox = imagettfbbox($this->fontSize, 0, $this->fontName, $this->textContent);

            //文字margin_right为5px,特此加5
            $fontW = $bbox[2] - $bbox[0] + 5;
            $fontH = abs($bbox[7] - $bbox[1]);

            if ($limitOption === true && $this->heightNew && $this->heightNew) {

                $this->fontX = ($this->widthNew > $fontW) ? $this->widthNew - $fontW : 0;
                $this->fontY = ($this->heightNew > $fontH) ? $this->heightNew - $fontH : 0;

            } else {

                $this->fontX = ($this->imageWidth > $fontW) ? $this->imageWidth - $fontW : 0;
                $this->fontY = ($this->imageHeight > $fontH) ? $this->imageHeight - $fontH : 0;
            }
        }

        return true;
    }

    /**
     * 常设置的文字颜色转换为图片信息.
     *
     * @return boolean
     */
    protected function handleFontColor() {

        if (empty($this->fontColor)) {
            $this->fontColor = array(255, 255, 255);
        }

        return imagecolorallocate($this->image, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
    }
    
    /**
     * 常设置的文字背景颜色转换为图片信息.
     */
    protected function handleFontBgColor(){
    	if (empty($this->fontBgColor)) {
    		$this->fontBgColor = array(128, 128, 128);
    	}
    	
    	return imagecolorallocate($this->image, $this->fontBgColor[0], $this->fontBgColor[1], $this->fontBgColor[2]);
    }

    /**
     * 根据图片原来的宽和高的比例,自适应性处理缩略图的宽度和高度
     *
     * @return boolean
     */
    protected function handleImageSize() {

        //当没有所生成的图片的宽度和高度设置时.
        if (!$this->width || !$this->height) {
            return false;
        }

        $perW = $this->width/$this->imageWidth;
        $perH = $this->height/$this->imageHeight;

        if (ceil($this->imageHeight*$perW)>$this->height) {
            $this->widthNew  = ceil($this->imageWidth*$perH);
            $this->heightNew = $this->height;
        } else {
            $this->widthNew  = $this->width;
            $this->heightNew = ceil($this->imageHeight*$perW);
        }

        return true;
    }

    /**
     * 生成图片的缩略图.
     *
     * @param string $url            原始图片路径
     * @param string $distName     生成图片的路径(注:无须后缀名)
     * @return boolean
     */
    public function makeLimitImage($image, $isUrl = true, $distName = null) {

        //参数分析
        if (!$image) {
            return false;
        }

    	//分析原图片.
        if($isUrl){
        	$this->parseImageInfo($image);
        }else {
        	$this->parseImageInfoFromString($image);
        }
        $this->handleImageSize();

        //新图片分析.
        $imageDist = imagecreatetruecolor($this->widthNew, $this->heightNew);

        //生成新图片.
        imagecopyresampled($imageDist, $this->image, 0, 0, 0, 0, $this->widthNew, $this->heightNew, $this->imageWidth, $this->imageHeight);

        $this->createImage($imageDist, $distName, $this->type);
        imagedestroy($imageDist);
        imagedestroy($this->image);

        return true;
    }

    /**
     * 生成目标图片.
     *
     * @param string $imageDist    原始图片的路径
     * @param string $distName        生成图片的路径
     * @param string $imageType    图片格式
     */
    protected function createImage($imageDist, $distName = null, $imageType) {

        //参数分析
        if (!$imageDist || !$imageType) {
            return false;
        }

        if (!is_null($distName)) {
            switch ($imageType) {

                case 'gif':
                    imagegif ($imageDist, $distName);
                    break;

                case 'jpg':
                    imagejpeg($imageDist, $distName);
                    break;

                case 'png':
                    imagepng($imageDist, $distName);
                    break;

                case 'bmp':
                    imagewbmp($imageDist, $distName);
                    break;
            }
        } else {
            switch ($imageType) {

                case 'gif':
                    header('Content-type:image/gif');
                    imagegif ($imageDist);
                    break;

                case 'jpg':
                    header('Content-type:image/jpeg');
                    imagejpeg($imageDist);
                    break;

                case 'png':
                    header('Content-type:image/png');
                    imagepng($imageDist);
                    break;

                case 'bmp':
                    header('Content-type:image/png');
                    imagewbmp($imageDist);
                    break;
            }
        }


        return true;
    }

    /**
     * 生成文字水印图片.
     *
     * @param stirng $imageUrl    背景图片的路径
     * @param string $distName    路径目标图片的
     * 
     * @return boolean
     */
    public function makeTextWatermark($image, $isUrl = true, $distName = null) {

        //参数判断
        if (!$image) {
            return false;
        }

        //分析原图片.
        if($isUrl){
        	$ret = $this->parseImageInfo($image);
        }else {
        	$ret = $this->parseImageInfoFromString($image);
        }
        if(false === $ret) return false;
       

        //当所要生成的文字水印图片有大小尺寸限制时(缩略图功能)
        if($this->width && $this->height) {

            $this->handleImageSize();
            //新图片分析.
            $imageDist = imagecreatetruecolor($this->widthNew, $this->heightNew);

            //生成新图片.
            imagecopyresampled($imageDist, $this->image, 0, 0, 0, 0, $this->widthNew, $this->heightNew, $this->imageWidth, $this->imageHeight);

            //所生成的图片进行分析.
            $this->handleWatermarkFontPlace(true);

            $fontColor = $this->handleFontColor();

            //生成新图片.
            imagettftext($imageDist, $this->fontSize, 0, $this->fontX, $this->fontY, $fontColor, $this->fontName, $this->textContent);
            $this->createImage($imageDist, $distName, $this->type);
            imagedestroy($imageDist);

        } else {

            //所生成的图片进行分析.
            $this->handleWatermarkFontPlace();

            $fontColor = $this->handleFontColor();
            
            $fontBgColor = $this->handleFontBgColor();

            //生成新图片.
            imagettftext($this->image, $this->fontSize, 0, $this->fontX + 1, $this->fontY + 1, $fontBgColor, $this->fontName, $this->textContent);
            imagettftext($this->image, $this->fontSize, 0, $this->fontX, $this->fontY, $fontColor, $this->fontName, $this->textContent);
            $this->createImage($this->image, $distName, $this->type);
        }

        imagedestroy($this->image);

        return true;
    }

    /**
     * 获取水印图片信息
     *
     * @return boolean
     */
    protected function handleWatermarkImage() {

        if ($this->image && !$this->waterImage) {

            $waterUrl = (!$this->imageUrl) ? DOIT_ROOT . 'views/source/watermark' . '.' . $this->type : $this->imageUrl;

            list($this->waterWidth, $this->waterHeight, $type) = getimagesize($waterUrl);

            switch ($type) {

                case 1:
                    $this->waterImage = imagecreatefromgif ($waterUrl);
                    break;

                case 2:
                    $this->waterImage = imagecreatefromjpeg($waterUrl);
                    break;

                case 3:
                    $this->waterImage = imagecreatefrompng($waterUrl);
                    break;

                case 4:
                    $this->waterImage = imagecreatefromwbmp($waterUrl);
                    break;
            }
        }

        return true;
    }

    /**
     * 调整水印区域的位置,默认位置距图片右下角边沿5像素.
     *
     * @return boolean
     */
    protected function handleWatermarkImagePlace($limitOption = false) {

        if (!$this->waterX || !$this->waterY) {

            if ($limitOption === true && $this->widthNew && $this->heightNew) {

                $this->waterX = ($this->widthNew - 5 > $this->waterWidth) ? $this->widthNew - $this->waterWidth - 5 : 0;
                $this->waterY = ($this->heightNew - 5 > $this->waterHeight) ? $this->heightNew - $this->waterHeight - 5 : 0;

            } else {

                $this->waterX = ($this->imageWidth-5 > $this->waterWidth) ? $this->imageWidth - $this->waterWidth - 5 : 0;
                $this->waterY = ($this->imageHeight-5 > $this->waterHeight) ? $this->imageHeight - $this->waterHeight - 5 : 0;
            }
        }

        return true;
    }

    /**
     * 生成图片水印.
     *
     * @param string $imageUrl    原始图片的路径
     * @param string $distName 生成图片的路径(注:不含图片后缀名)
     * @return boolean
     */
    public function makeImageWatermark($image, $isUrl = true, $distName = null) {

        //参数分析
        if (!$image) {
            return false;
        }

    	//分析原图片.
        if($isUrl){
        	$this->parseImageInfo($image);
        }else {
        	$this->parseImageInfoFromString($image);
        }

        //水印图片的透明度参数
        $this->alpha = empty($this->alpha) ? 85 : $this->alpha;

        //对水印图片进行信息分析.
        $this->handleWatermarkImage();

        if ($this->width && $this->height) {

            $this->handleImageSize();
            //新图片分析.
            $imageDist = imagecreatetruecolor($this->widthNew, $this->heightNew);

            //生成新图片.
            imagecopyresampled($imageDist, $this->image, 0, 0, 0, 0, $this->widthNew, $this->heightNew, $this->imageWidth, $this->imageHeight);

            //分析新图片的水印位置.
            $this->handleWatermarkImagePlace(true);

            //生成新图片.
            imagecopymerge($imageDist, $this->waterImage, $this->waterX, $this->waterY, 0, 0, $this->waterWidth, $this->waterHeight, $this->alpha);
            $this->createImage($imageDist, $distName, $this->type);
            imagedestroy($imageDist);

        } else {

            //分析新图片的水印位置.
            $this->handleWatermarkImagePlace();

            //生成新图片.
            imagecopymerge($this->image, $this->waterImage, $this->waterX, $this->waterY, 0, 0, $this->waterWidth, $this->waterHeight, $this->alpha);
            $this->createImage($this->image, $distName, $this->type);
        }

        imagedestroy($this->image);
        imagedestroy($this->waterImage);

        return true;
    }
    
    public function cropImage($image, $isUrl = false, $target = false, $s_x, $s_y, $s_w, $s_h, $target_width, $target_height)
    {
    	//参数分析
    	if (!$image) {
    		return false;
    	}
    	
    	//分析原图片.
    	if($isUrl){
    		$ret = $this->parseImageInfo($image);
    	}else {
    		$ret = $this->parseImageInfoFromString($image);
    	}
    	if($ret === false ){
    		Log::notice('222');
    	}
    	
    	if( ($target_height / $target_width) != ($s_h / $s_w)) {
    		Log::error('crop size not compatible');
    		return false;
    	}
    
    	$target_image  = imagecreatetruecolor($target_width, $target_height);
    	$cropped_image = imagecreatetruecolor($s_w, $s_h);
    
    	// 裁剪
    	if(false === imagecopy($cropped_image, $this->image, 0, 0, $s_x, $s_y, $s_w, $s_h)){
    		return false;
    	}
    	// 缩放
    	if(false === imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $s_w, $s_h)){
    		return false;
    	}
    
    	if(false === $this->createImage($target_image, $target, 'png')){
    		return false;
    	}
    	
    	imagedestroy($this->image);
    	imagedestroy($target_image);
    	imagedestroy($cropped_image);
    }
}