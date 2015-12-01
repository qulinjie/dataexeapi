<?php
/**
 * @file:  session_redis.ini.php
 * @brief:  redis 连接配置文件, 和KEY前缀, 值失效时间配置
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-08-11
 */

return array(
		'host' => '120.25.1.102',		// redis 实例主机
		'port' => 6379,				// redis 连接端口
		'timeout' => '',			// 连接超时时间？
		'keyPrefix' => 'PHPSESSID:',// KEY 前缀
		'expire' => '86400',		// 过期时间一天
		'options' => array( 
			//'' => '',  // 其他REDIS 配置
		),
);

