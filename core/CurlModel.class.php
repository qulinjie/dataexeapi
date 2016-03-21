<?php
/**
 * @file:  CurlModel.class.php
 * @brief:  
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-09-14
 */

class CurlModel 
{
	protected static $_redis;
	protected static $_keyPrefix;
	protected static $_expire;

    /**
     * 用CURL模拟提交数据
     *
     * @param string $url        post所要提交的网址
     * @param array  $data        所要提交的数据
     * @param string $proxy        代理设置
     * @param integer $expire    所用的时间限制
     * @return string
     */
	protected function postRequest( $url, $data=[], $header=[], $proxy=null, $expire=36000 )
	{
        if ( !$url ) return false;

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        // 设置代理
        if (!is_null($proxy)) {
            curl_setopt ( $ch, CURLOPT_PROXY, $proxy );
        }
        $isSSL = substr($url, 0, 8) == 'https://' ? true : false;
        if ( $isSSL ) {
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );// 对认证证书来源的检查
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 1 );// 从证书中检查SSL加密算法是否存在
        }
    
		// 设立临存目录
		$cookieFile =  $this->getCookieFile();
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );

		// 以临存目录名，为redis key尝试获取cookie信息
        $cookieInfo = $this-> getCURLCookieInfoFromRedis( $cookieFile );
        curl_setopt($ch, CURLOPT_COOKIE, $cookieInfo);
		

        //curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		//curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
		
    
        // 设置浏览器
		curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		// 不用浏览器？
        //curl_setopt( $ch, CURLOPT_HEADER, TRUE );
		//curl_setopt( $ch, CURLOPT_NOBODY, FALSE );

    
        // 设置请求header
        if ( !empty($header) ) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        }
        
		// POST发送数据
        curl_setopt( $ch, CURLOPT_POST, true );//发送一个常规的Post请求
        curl_setopt( $ch,  CURLOPT_POSTFIELDS, $data );//Post提交的数据包
    
        // 使用自动跳转
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $expire );
    
		// 执行发送CURL
        $response = curl_exec( $ch );
		//print_r( $response );
		//exit;
        $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        if ( $httpCode != 200 ) {
            echo 'curl http query fail! code error, code:' . $httpCode . '<BR>';
            return false;
        }
        curl_close( $ch );

		$this-> saveCURLCookieToRedis( $cookieFile );
        return json_decode( $response, true );
    }
    
	public function sendRequest( $interface, $data=[] )
	{
		if ( !$interface ) {
			return false;
		}
        $base_data = [ 'caller'=>'test', 'callee'=>'ddmg_server', 'eventid'=>rand()%10000, 'timestamp'=>time() ];
        $base_data['data'] = $data;
		$url = $this->getUrl( $interface );
        $ret = $this->postRequest( $url, $base_data );
        return $ret;
    }
    
	protected function getCookieFile()
	{
        $session = Controller::instance('session');
        $session_id = $session->get_id();
		if ( !is_dir( DOIT_ROOT . 'curlCookieFile' ) ) {
			mkdir(  DOIT_ROOT . 'curlCookieFile', 0755  );
		}
        $cookieFile = DOIT_ROOT . 'curlCookieFile' . DIRECTORY_SEPARATOR . $session_id;
        return $cookieFile;
    }

	protected function getUrl( $interface )
	{
		return 'http://localhost/ddmg_server/' . $interface;
	}

	protected function getCURLCookieInfoFromLocalFile( $cookieFile )
	{
		if ( !file_exists( $cookieFile ) ) {
			return false;
		}
        $cookieFileContent = file($cookieFile,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $set_cookies_value = '';
        foreach ( $cookieFileContent as $lineStr ) {
            $content = trim( $lineStr );
            if ( !$content || '#'==substr($content,0,1) ) {
                continue;
            }
            $fieldArray = explode( chr(9) , $content );
            if ( 7 > count($fieldArray) ){
                continue;
            }
			if ( $fieldArray[6]=='czowOiIiOw%3D%3D' || $fieldArray[6]=='czowOiIiOw==' ) {
				continue;
			}
            $set_cookies_value = $set_cookies_value . $fieldArray[5] . '=' . $fieldArray[6] . ';';
        }
		@unlink( $cookieFile );
		return $set_cookies_value;
	}

	protected function saveCURLCookieToRedis( $cookieFile )
	{
		if ( !$cookieFile ) {
			return false;
		}
        $cookieInfo = $this-> getCURLCookieInfoFromRedis( $cookieFile );
		if ( !$cookieInfo ) {
			$cookieInfo = $this-> getCURLCookieInfoFromLocalFile( $cookieFile );
		}
		$key = $this->getCURLCookieRedisKey( $cookieFile );

		self::$_redis->set( $key, $cookieInfo );
		self::$_redis->expire( $key, self::$_expire );
		return true;
	}

	protected function getCURLCookieInfoFromRedis( $cookieFile )
	{
		if ( !$cookieFile ) {
			return false;
		}
		$key = $this->getCURLCookieRedisKey( $cookieFile );
		if ( !$key ) {
			return false;
		}
		return self::$_redis->get( $key ) ;
	}

	private function getCURLCookieRedisKey( $cookieFile )
	{
		return self::$_keyPrefix . basename( $cookieFile );
	}

	private static function getRedis()
	{
		if ( self::$_redis )
			return self::$_redis;

		$conf = Controller::getConfig( 'curl_cookie_redis' );
		foreach ( ['host', 'port' ] as $val ) {
			if ( !$conf[$val] ) return false;
		}

		$conn = new Redis();
		if ( $conf['timeout'] ) {
			$res = $conn->connect( $conf['host'], $conf['port'], $conf['timeout'] );
		} else {
			$res = $conn->connect( $conf['host'], $conf['port'] );
		}
		if ( !is_array($conf['options']) ) {
			$conf['options'] = [];
		}
		if ( $conf['options'] ) {
			foreach ( $conf['options'] as $k => $v ) {
				$conn->setOption( $k, $v );
			}
		}
		// 默认KEY前缀
		self::$_keyPrefix = $conf['keyPrefix'] ? $conf['keyPrefix'] : 'CURL:COOKIE:';
		// 默认过期时间
		self::$_expire = $conf['expire'] ? $conf['expire'] : '86400';
		return $conn;
	}



	public function sendRequestErp($interface, $data)
	{
        Log::error('sendRequestErp====>>>>interface=##' . $interface . '##');
        Log::error('sendRequestErp====>>>>request_data=##' . json_encode($data) . '##');
    
        $header[] = 'Content-type: application/json;charset=UTF-8';
        
		$ret = self::postRequest($interface, json_encode($data), $header);

        Log::error('sendRequestErp====>>>>reponse=##' . json_encode($ret) . '##');
        return $ret;
    }
	

	public static function clearCRULRedis(  )
	{
        $session = Controller::instance('session');
        $session_id = $session->get_id();
		$redis = self::getRedis();
		$key = self::$_keyPrefix . $session_id;
		$res = $redis->delete( $key );
	}


	public function __construct(  )
	{
		self::$_redis = $this->getRedis();
	}

}
