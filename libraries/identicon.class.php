<?php 
class identicon
{
	/**
	 * Get an Identicon PNG image data
	 *
	 * @param string  $string
	 * @param integer $size
	 * @param string  $color
	 * @param string  $backgroundColor
	 *
	 * @return string
	 */
	public function getImageData($string, $size = 64, $margin = 0, $backColorArr = null)
	{
		if(! $string) return false;
		if(! is_int($size)) return false;
		
		$pixelRatio = $size / 5;
		
		// prepare image
		$generatedImage = imagecreatetruecolor($pixelRatio * 5 + $margin * 2, $pixelRatio * 5 + $margin * 2);
	
		if (null === $backColorArr) {
			$background = imagecolorallocate($generatedImage, 0, 0, 0);
			imagecolortransparent($generatedImage, $background);
		} else {
			$background = imagecolorallocate($generatedImage, $backColorArr[0], $backColorArr[1], $backColorArr[2]);
			imagefill($generatedImage, 0, 0, $background);
		}
	
		$hash = md5($string);
		
		$arrayOfSquare = array();
		
		preg_match_all('/(\w)(\w)/', $hash, $chars);
		foreach ($chars[1] as $i => $char) {
			if($i < 15){//fix issue #7
				if ($i % 3 == 0) {
					$arrayOfSquare[$i/3][0] = (bool) intval(round(hexdec($char)/10));
					$arrayOfSquare[$i/3][4] = (bool) intval(round(hexdec($char)/10));
				} elseif ($i % 3 == 1) {
					$arrayOfSquare[$i/3][1] = (bool) intval(round(hexdec($char)/10));
					$arrayOfSquare[$i/3][3] = (bool) intval(round(hexdec($char)/10));
				} else {
					$arrayOfSquare[$i/3][2] = (bool) intval(round(hexdec($char)/10));
				}
				ksort($arrayOfSquare[$i/3]);
			}
		}
		
		$rgbColor = array();
		
		$rgbColor[0] = hexdec(array_pop($chars[1]))*16;
		$rgbColor[1] = hexdec(array_pop($chars[1]))*16;
		$rgbColor[2] = hexdec(array_pop($chars[1]))*16;
		
		// prepage color
		$gdColor = imagecolorallocate($generatedImage, $rgbColor[0], $rgbColor[1], $rgbColor[2]);
	
		// draw content
		foreach ($arrayOfSquare as $lineKey => $lineValue) {
			foreach ($lineValue as $colKey => $colValue) {
				if (true === $colValue) {
					imagefilledrectangle($generatedImage,
					$colKey * $pixelRatio + $margin,
					$lineKey * $pixelRatio + $margin,
					($colKey + 1) * $pixelRatio + $margin,
					($lineKey + 1) * $pixelRatio + $margin, $gdColor);
				}
			}
		}
	
		ob_start();
		imagepng($generatedImage);
		$imageData = ob_get_contents();
		ob_end_clean();
		
		return $imageData;
	}

	/**
	 * Get an Identicon PNG image data as base 64 encoded
	 *
	 * @param string  $string
	 * @param integer $size
	 * @param string  $color
	 * @param string  $backgroundColor
	 *
	 * @return string
	 */
	public function getImageDataUri($string, $size = 64, $margin = 0, $backColorArr = null)
	{
		return sprintf('data:image/png;base64,%s', base64_encode($this->getImageData($string, $size, $margin, $backColorArr)));
	}
}
