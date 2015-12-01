<?php
/**
 * @file:  session_redis.class.php
 * @brief:  redis 共享存储 session 解决
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-08-11
 */

/*
 * @example:
 *
 * 		include 'session_redis.class.php';
 * 		session_redis::init();
 * 		session_start();
 */
class session_redis
{
	protected static $redis;		// redis 连接对象
	protected static $expire;		// 数据过期时间
	protected static $keyPrefix;	// redis里sessionKey的前缀

	public static function init(  )
	{
		// 连接设置 redis 主机
		self::$redis = self::getRedis();
		if ( !self::$redis ) return false;

		ini_set( 'session.use_trans_sid', 0 ); 		// 不使用GET/POST变量方式
		ini_set( 'session.gc_maxlifetime', 3600 ); 	// 垃圾回收最大生存时间
		ini_set( 'session.use_cookies', 1 ); 		// 使用COOKIE保存SESSIONID的方式

		//$domain = '.ddmg.com';   
		//ini_set( 'session.cookie_domain', $domain );// 多主机共享保存SESSION_ID的域名  -- 这个还没想好设什么，反正不能写死

		session_module_name( 'user' ); 				// 将session.save_handler 设置为user定制，而不是默认的files 文件存储方式
		session_set_save_handler(
			'session_redis::open',
			'session_redis::close',
			'session_redis::read',
			'session_redis::write',
			'session_redis::destroy',
			'session_redis::gc'
	   	);
	}

	private static function getRedis()
	{
		if ( self::$redis )
			return self::$redis;

		$conf = Controller::getConfig( 'session_redis' );
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
		foreach ( $conf['options'] as $k => $v ) {
			$conn->setOption( $k, $v );
		}
		// 默认KEY前缀
		self::$keyPrefix = $conf['keyPrefix'] ? $conf['keyPrefix'] : 'PHPSESSID:';
		// 默认过期时间
		self::$expire = $conf['expire'] ? $conf['expire'] : '86400';
		return $conn;
	}

	public static function open(){}

	public static function close()
	{
		self::$redis = null;
	}

	public static function read( $id )
	{
		$key = self::$keyPrefix . $id;
		$sessData = self::$redis->get( $key ); // 读取数据
		self::$redis->expire( $key, self::$expire ); // 重置超时
		return $sessData;
	}

	public static function write( $id, $data )
	{
		$key = self::$keyPrefix . $id;
		self::$redis->set( $key, $data) ;
		self::$redis->expire( $key, self::$expire );
		return true;
	}

	public static function destroy( $id )
	{
		$key = self::$keyPrefix . $id;
		return self::$redis->delete( $key );
	}

	public static function gc()
	{
		return true;
	}

}
