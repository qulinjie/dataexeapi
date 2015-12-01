<?php 
/**
 * encrypt.class.php
 *
 * Form令牌加密,防止伪造表单提交及数据的加密,解密
 * @author tommy <streen003@gmail.com>, 付超群
 * @copyright Copyright (c) 2010 Tommycode Studio, ColaPHP
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: encrypt.class.php 1.0 2011-12-27 22:20:14Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class encrypt extends Base {

	/**
	 * 已使用的token列表
	 */
	protected static $_droped_token_session_key = 'xpp_droped_token_session_key';
	
	/**
	 * 处理中token列表
	 */
	protected static $_processing_token_session_key = 'xpp_processing_token_session_key';
	
	
    /**
     * 时间周期
     *
     * @var integer
     */
    protected static $_liftTime = 7200;

    /**
     * 加密字符串(密钥)
     *
     * @var string
     */
    protected static $_key = 'your-secret-code';

    /**
     * config data
     *
     * @var array
     */
    protected $_config = array();


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

    	$conf = Controller::getConfig('conf');
    	
        //set config infomation
        $this->_config = array(
        'hash'      => 'sha1',
        'xor'       => false,
        'mcrypt'    => function_exists('mcrypt_encrypt') ? true : false,
        'noise'     => true,
        'cipher'    => MCRYPT_RIJNDAEL_256,
        'mode'      => MCRYPT_MODE_ECB,
        'rsa_modulus'	=>	$conf['rsa_modulus'],
        'rsa_private'	=>	$conf['rsa_private'],
        'rsa_public'	=>	$conf['rsa_public'],
        'rsa_key_len' => $conf['rsa_key_len']
        );

        self::$_liftTime = $conf['token_life_time'];
        self::$_droped_token_session_key = $conf['xpp_droped_token_session_key'];
        self::$_processing_token_session_key = $conf['xpp_processing_token_session_key'];
        
        return true;
    }
    
    
    /*
     * PHP implementation of the RSA algorithm
    * (C) Copyright 2004 Edsko de Vries, Ireland
    *
    * Licensed under the GNU Public License (GPL)
    *
    * This implementation has been verified against [3]
    * (tested Java/PHP interoperability).
    *
    * References:
    * [1] "Applied Cryptography", Bruce Schneier, John Wiley & Sons, 1996
    * [2] "Prime Number Hide-and-Seek", Brian Raiter, Muppetlabs (online)
    * [3] "The Bouncy Castle Crypto Package", Legion of the Bouncy Castle,
    *      (open source cryptography library for Java, online)
    * [4] "PKCS #1: RSA Encryption Standard", RSA Laboratories Technical Note,
    *      version 1.5, revised November 1, 1993
    */
    
    /*
     * Functions that are meant to be used by the user of this PHP module.
    *
    * Notes:
    * - $key and $modulus should be numbers in (decimal) string format
    * - $message is expected to be binary data
    * - $keylength should be a multiple of 8, and should be in bits
    * - For rsa_encrypt/rsa_sign, the length of $message should not exceed
    *   ($keylength / 8) - 11 (as mandated by [4]).
    * - rsa_encrypt and rsa_sign will automatically add padding to the message.
    *   For rsa_encrypt, this padding will consist of random values; for rsa_sign,
    *   padding will consist of the appropriate number of 0xFF values (see [4])
    * - rsa_decrypt and rsa_verify will automatically remove message padding.
    * - Blocks for decoding (rsa_decrypt, rsa_verify) should be exactly
    *   ($keylength / 8) bytes long.
    * - rsa_encrypt and rsa_verify expect a public key; rsa_decrypt and rsa_sign
    *   expect a private key.
    */
    
    /**
     * 于2010-11-12 1:06分于LONELY修改
    */
    public function rsa_encrypt($message, $public_key, $modulus, $keylength)
    {
    	$padded = $this->add_PKCS1_padding($message, true, $keylength / 8);
    	$number = $this->binary_to_number($padded);
    	$encrypted = $this->pow_mod($number, $public_key, $modulus);
    	$result = $this->number_to_binary($encrypted, $keylength / 8);
    
    	return $result;
    }
    
    public function rsa_decrypt($message, $private_key, $modulus, $keylength)
    {
    	$number = $this->binary_to_number($message);
    	$decrypted = $this->pow_mod($number, $private_key, $modulus);
    	$result = $this->number_to_binary($decrypted, $keylength / 8);
    	return $this->remove_PKCS1_padding($result, $keylength / 8);
    }
    
    public function rsa_sign($message, $private_key, $modulus, $keylength)
    {
    	$padded = $this->add_PKCS1_padding($message, false, $keylength / 8);
    	$number = $this->binary_to_number($padded);
    	$signed = $this->pow_mod($number, $private_key, $modulus);
    	$result = $this->number_to_binary($signed, $keylength / 8);
    
    	return $result;
    }
    
    public function rsa_verify($message, $public_key, $modulus, $keylength)
    {
    	return $this->rsa_decrypt($message, $public_key, $modulus, $keylength);
    }
    
    public function rsa_kyp_verify($message, $public_key, $modulus, $keylength)
    {
    	$number = $this->binary_to_number($message);
    	$decrypted = $this->pow_mod($number, $public_key, $modulus);
    	$result = $this->number_to_binary($decrypted, $keylength / 8);
    
    	return $this->remove_KYP_padding($result, $keylength / 8);
    }
    
    /*
     * Some constants
    */
    private static $BCCOMP_LARGER = 1;
    
    /*
     * The actual implementation.
    * Requires BCMath support in PHP (compile with --enable-bcmath)
    */
    
    //--
    // Calculate (p ^ q) mod r
    //
    // We need some trickery to [2]:
    //   (a) Avoid calculating (p ^ q) before (p ^ q) mod r, because for typical RSA
    //       applications, (p ^ q) is going to be _WAY_ too large.
    //       (I mean, __WAY__ too large - won't fit in your computer's memory.)
    //   (b) Still be reasonably efficient.
    //
    // We assume p, q and r are all positive, and that r is non-zero.
    //
    // Note that the more simple algorithm of multiplying $p by itself $q times, and
    // applying "mod $r" at every step is also valid, but is O($q), whereas this
    // algorithm is O(log $q). Big difference.
    //
    // As far as I can see, the algorithm I use is optimal; there is no redundancy
    // in the calculation of the partial results.
    //--
    private function pow_mod($p, $q, $r)
    {
    	// Extract powers of 2 from $q
    	$factors = array();
    	$div = $q;
    	$power_of_two = 0;
    	while(bccomp($div, "0") == self::$BCCOMP_LARGER)
    	{
    		$rem = bcmod($div, 2);
    		$div = bcdiv($div, 2);
    
    		if($rem) array_push($factors, $power_of_two);
    		$power_of_two++;
    	}
    
    	// Calculate partial results for each factor, using each partial result as a
    	// starting point for the next. This depends of the factors of two being
    	// generated in increasing order.
    	$partial_results = array();
    	$part_res = $p;
    	$idx = 0;
    	foreach($factors as $factor)
    	{
    		while($idx < $factor)
    		{
    			$part_res = bcpow($part_res, "2");
    			$part_res = bcmod($part_res, $r);
    
    			$idx++;
    		}
    
    		array_push($partial_results, $part_res);
    	}
    
    	// Calculate final result
    	$result = "1";
    	foreach($partial_results as $part_res)
    	{
    		$result = bcmul($result, $part_res);
    		$result = bcmod($result, $r);
    	}
    
    	return $result;
    }
    
    //--
    // Function to add padding to a decrypted string
    // We need to know if this is a private or a public key operation [4]
    //--
    private function add_PKCS1_padding($data, $isPublicKey, $blocksize)
    {
    	$pad_length = $blocksize - 3 - strlen($data);
    
    	if($isPublicKey)
    	{
    		$block_type = "\x02";
    
    		$padding = "";
    		for($i = 0; $i < $pad_length; $i++)
    		{
    			$rnd = mt_rand(1, 255);
    			$padding .= chr($rnd);
    		}
    	}
    	else
    	{
    		$block_type = "\x01";
    		$padding = str_repeat("\xFF", $pad_length);
    	}
    
    	return "\x00" . $block_type . $padding . "\x00" . $data;
    }
    
    //--
    // Remove padding from a decrypted string
    // See [4] for more details.
    //--
    private function remove_PKCS1_padding($data, $blocksize)
    {
    	//以下部分于原版的RSA有所不同,修复了原版的一个BUG
    	//assert(strlen($data) == $blocksize);
    	$data = substr($data, 1);
    
    	// We cannot deal with block type 0
    	if($data{0} == '\0')
    		die("Block type 0 not implemented.");
    
    	// Then the block type must be 1 or 2
    	//assert(($data{0} == "\x01") || ($data{0} == "\x02"));
    
    	//	echo $data;
    	// Remove the padding
    	$i=1;
    	while (1){
    		$offset = strpos($data, "\0", $i);
    		if(!$offset){
    			$offset=$i;
    			break;
    		}
    		$i=$offset+1;
    	}
    	//$offset = strpos($data, "\0", 100);
    	return substr($data, $offset);
    }
    
    //--
    // Remove "kyp" padding
    // (Non standard)
    //--
    private function remove_KYP_padding($data, $blocksize)
    {
    	assert(strlen($data) == $blocksize);
    
    	$offset = strpos($data, "\0");
    	return substr($data, 0, $offset);
    }
    
    //--
    // Convert binary data to a decimal number
    //--
    private function binary_to_number($data)
    {
    	$base = "256";
    	$radix = "1";
    	$result = "0";
    
    	for($i = strlen($data) - 1; $i >= 0; $i--)
    	{
    		$digit = ord($data{$i});
    		$part_res = bcmul($digit, $radix);
    		$result = bcadd($result, $part_res);
    		$radix = bcmul($radix, $base);
    	}
    
    	return $result;
    }
    
    //--
    // Convert a number back into binary form
    //--
    private function number_to_binary($number, $blocksize)
    {
    	$base = "256";
    	$result = "";
    
    	$div = $number;
    	while($div > 0)
    	{
    		$mod = bcmod($div, $base);
    		$div = bcdiv($div, $base);
    
    		$result = chr($mod) . $result;
    	}
    
    	return str_pad($result, $blocksize, "\x00", STR_PAD_LEFT);
    }
    
    
    /**
     * 16 to 2
     * @param unknown_type $hexString
     * @return string|unknown
     */
    public function convert($hexString)
    {
    	$hexLenght = strlen($hexString);
    	// only hex numbers is allowed
    	if ($hexLenght % 2 != 0 || preg_match("/[^\da-fA-F]/",$hexString)) return FALSE;
    
    	unset($binString);
    	for ($x = 1; $x <= $hexLenght/2; $x++)
    	{
    		$binString .= chr(hexdec(substr($hexString,2 * $x - 2,2)));
    	}
    
    	return $binString;
    }
    
    /**
     * base64转hex
     */
    private static $B64_MAP = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	private static $B64_PAD = '=';
	private static $HEX_MAP = "0123456789abcdef";
    public function b642hex($b64Str) {
		$ret = '';
		$k = 0; // b64 state
		
		for($i = 0; $i < strlen ( $b64Str ); $i ++) {
			if ($b64Str [$i] == self::$B64_PAD)
				break;
			$v = strpos ( self::$B64_MAP, $b64Str [$i] );
			if (false === $v) {
				return false;
			}
			if ($k == 0) {
				$ret .= self::$HEX_MAP [$v >> 2];
				$k = 1;
				$slop = $v & 3;
			} else if ($k == 1) {
				$ret .= self::$HEX_MAP [($slop << 2) | ($v >> 4)];
				$k = 2;
				$slop = $v & 0xf;
			} else if ($k == 2) {
				$ret .= self::$HEX_MAP [$slop];
				$ret .= self::$HEX_MAP [$v >> 2];
				$k = 3;
				$slop = $v & 3;
			} else {
				$ret .= self::$HEX_MAP [($slop << 2) | ($v >> 4)];
				$ret .= self::$HEX_MAP [$v & 0xf];
				$k = 0;
				$slop = 0;
			}
		}
		if ($k == 1) {
			$ret .= self::$HEX_MAP [$slop << 2];
		}
		return $ret;
	}

	/**
	 * hex转base64
	 */
	public function hex2b64($hexStr) {
		$ret = '';
		for($i = 0; $i + 3 < strlen ( $hexStr ); $i += 3) {
			// get int value
			$a = strpos ( self::$HEX_MAP, $hexStr [$i] );
			$b = strpos ( self::$HEX_MAP, $hexStr [$i + 1] );
			$c = strpos ( self::$HEX_MAP, $hexStr [$i + 2] );
			$v = $a * 16 * 16 + $b * 16 + $c;
			$ret .= self::$B64_MAP [$v >> 6];
			$ret .= self::$B64_MAP [$v & 0x3f];
		}
		if ($i + 1 == strlen ( $hexStr )) {
			$a = strpos ( self::$HEX_MAP, $hexStr [$i] );
			$ret .= self::$B64_MAP [$a << 2];
		} else if ($i + 2 == strlen ( $hexStr )) {
			$a = strpos ( self::$HEX_MAP, $hexStr [$i] );
			$b = strpos ( self::$HEX_MAP, $hexStr [$i + 1] );
			$v = $a * 16 + $b;
			$ret .= self::$B64_MAP [$v >> 2];
			$ret .= self::$B64_MAP [($v & 3) << 4];
		}
		while ( strlen ( $ret ) & 3 > 0 ) {
			$ret .= self::$B64_PAD;
		}
		return $ret;
	}
	
	
    /**
     * 设置或获取配置参数($_config)信息
     *
     * @access public
     * @param mixed $key 键值
     * @param mixed $value 参数值
     * @return mixed
     */
    public function config($key = null, $value = null) {

        if (is_null($key)) {
            return $this->_config;
        }

        if (is_array($key)) {
            $this->_config = $key + $this->_config;
            return $this;
        }

        if (is_null($value)) {
            return $this->_config[$key];
        }

        $this->_config[$key] = $value;
    }

    /**
     * rsa 公钥加密
     */
    public function rsa_encode($str){
    	$encrypted = $this->rsa_encrypt($str, 
    			$this->_config['rsa_public'], 
    			$this->_config['rsa_modulus'], 
    			$this->_config['rsa_key_ken']);
    	$str= bin2hex($encrypted);//bin data to hex data
    	return $str;
    }
    
    /**
     * rsa 私钥解密
     */
    public function rsa_decode($str, $fromJs = false){
    	$encrypted=$this->convert($str); //hex data to bin data
    	$decrypted = $this->rsa_decrypt($encrypted, 
    			$this->_config['rsa_private'], 
    			$this->_config['rsa_modulus'], 
    			$this->_config['rsa_key_len']);
    	return $decrypted;
    }
    
    /**
     * 加密
     *
     * @access public
     * @param string $str 待加密的字符串
     * @param string $key 密钥
     * @return string
     */
    public function encode($str, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if ($this->_config['xor']) {
            $str = $this->_xorEncode($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptEncode($str, $key);
        }

        if ($this->_config['noise']) {
            $str = $this->_noise($str, $key);
        }

        return base64_encode($str);
    }

    /**
     * 解密
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public function decode($str, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $str)) {
            return false;
        }

        $str = base64_decode($str);

        if ($this->_config['noise']) {
            $str = $this->_denoise($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptDecode($str, $key);
        }

        if ($this->_config['xor']) {
            $str = $this->_xorDecode($str, $key);
        }

        return $str;
    }

    /**
     * Mcrypt encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptEncode($str, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return mcrypt_encrypt($cipher, $key, $str, $mode, $vect);
    }

    /**
     * Mcrypt decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptDecode($str, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return rtrim(mcrypt_decrypt($cipher, $key, $str, $mode, $vect), "\0");
    }

    /**
     * XOR encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorEncode($str, $key) {

        $rand = $this->_config['hash'](rand());
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $r     = substr($rand, ($i % strlen($rand)), 1);
            $code .= $r . ($r ^ substr($str, $i, 1));
        }

        return $this->_xor($code, $key);
    }

    /**
     * XOR decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorDecode($str, $key) {

        $str = $this->_xor($str, $key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $code .= (substr($str, $i++, 1) ^ substr($str, $i, 1));
        }

        return $code;
    }

    /**
     * XOR
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xor($str, $key) {

        $hash = $this->_config['hash']($key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $code .= substr($str, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
        }

        return $code;
    }

    /**
     * Noise
     *
     * @see http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _noise($str, $key) {

        $hash = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen = strlen($str);
        $code = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $code .= chr((ord($str[$i]) + ord($hash[$j])) % 256);
        }

        return $code;
    }

    /**
     * Denoise
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _denoise($str, $key) {

        $hash = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen = strlen($str);
        $code = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $temp = ord($str[$i]) - ord($hash[$j]);
            if ($temp < 0) $temp = $temp + 256;
            $code .= chr($temp);
        }

        return $code;
    }

    /**
     * 生成随机码
     *
     * @access public
     * @param integer $length 随机码长度 (0~32)
     * @return string
     */
    public static function randCode($length = 5) {

        //参数分析
        $length = (int)$length;
        $length = ($length > 32) ? 32 : $length;

        $code  = md5(uniqid(mt_rand(), true));
        $start = mt_rand(0, 32 - $length);

        return substr($code, $start, $length);
    }

    /**
     * 生成令牌密码
     *
     * @access public
     * @param string $code 所要加密的字符(也可以是随机的)
     * @param string $lifeTime 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     * @return string
     */
    public static function tokenCode($data, $lifeTime = null, $key = null) {

        //参数分析
        if (!$data) {
            return false;
        }
        //设置生存周期
        if (!is_null($lifeTime)) {
            $lifeTime = (int)$lifeTime;
            if ($lifeTime) {
                self::$_liftTime = $lifeTime;
            }
        }
        $per  = ceil(time() / self::$_liftTime);
        //设置密钥
        if (!is_null($key)) {
            self::$_key = $key;
        }

        return hash_hmac('md5', $per . $data, self::$_key);
    }

    /**
     * 获取已丢弃令牌
     */
    public  static function getDropTokenCode(){
    	$session = Controller::instance('session');
    	$token_arr = $session->get(self::$_droped_token_session_key);
    	if(!$token_arr || !array($token_arr)){
    		return array();
    	}
    	return $token_arr;
    }
    
    /**
     * 获取处理中的令牌,避免重复提交
     */
    public static function getProcessingTokenCode(){
    	$session = Controller::instance('session');
    	$token_arr = $session->get(self::$_processing_token_session_key);
    	if(! $token_arr || !array($token_arr)){
    		return array();
    	}
    	return $token_arr;
    }
    
    /**
     * 把令牌放到处理令牌中
     */
    public static function processTokenCode($tokenCode){
    	$session = Controller::instance('session');
    	$token_arr = self::getProcessingTokenCode();
    	Log::notice('process_drop:' . var_export($token_arr, true));
    	if(in_array($tokenCode, $token_arr)){
    		//相同的动作处理中
    		return false;
    	}
    	$token_arr [] = $tokenCode;
    	$session->set(self::$_processing_token_session_key, $token_arr);
    	return true;
    }
    
    /**
     * 把令牌从处理令牌中删除
     */
    public static function deleteProcessTokenCode($tokenCode){
    	$session = Controller::instance('session');
    	$token_arr_process = self::getProcessingTokenCode();
    	$token_arr_process = array_values(array_diff($token_arr_process, array($tokenCode)));
    	$session->set(self::$_processing_token_session_key, $token_arr_process);
    }
    
    /**
     * 丢弃令牌
     */
    public static function dropTokenCode($tokenCode){
    	$session = Controller::instance('session');
    	$token_arr_process = self::getProcessingTokenCode();
    	//从处理队列中删除
    	$token_arr_process = array_values(array_diff($token_arr_process, array($tokenCode)));
    	$session->set(self::$_processing_token_session_key, $token_arr_process);
    	
    	$token_arr = self::getDropTokenCode();
    	$token_arr [] = $tokenCode;
    	$session->set(self::$_droped_token_session_key, $token_arr);
    	Log::notice('drop_drop:' . var_export($token_arr, true));
    }
    
    /**
     * 检查令牌是否丢弃
     */
    public static function isTokenDrop($tokenCode){
    	$session = Controller::instance('session');
    	$token_arr = self::getDropTokenCode();
    	Log::notice('token_drop:' . var_export($token_arr, true));
    	return in_array($tokenCode, $token_arr);
    }
    
    /**
     * 令牌密码验证
     *
     * @access public
     * @param string $data 所要验证的数据
     * @param string $tokenCode 所要验证的加密字符串
     * @param string $lifeTime 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     * @return boolean
     */
    public static function tokenValidate($data, $tokenCode, $lifeTime = null, $key = null) {

        //参数分析
        if (!$data || !$tokenCode) {
            return false;
        }
        //设置生存周期
        if (!is_null($lifeTime)) {
            $lifeTime = (int)$lifeTime;
            if ($lifeTime) {
                self::$_liftTime = $lifeTime;
            }
        }
        $per  = ceil(time() / self::$_liftTime);
        //设置密钥
        if (!is_null($key)) {
            self::$_key = $key;
        }
        $code = hash_hmac('md5', $per . $data, self::$_key);

        return ($code == $tokenCode) ? true : false;
    }
}