<?php
/**
 * @file:  curl_header_redis.ini.php
 * @brief:  CURL 接口 HEADER 信息存 SESSION
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-09-14
 */


return array(
		'host' => '120.25.1.102',		// redis 实例主机
		'port' => 6379,				// redis 连接端口
		'timeout' => '',			// 连接超时时间？
		'keyPrefix' => 'CURL:COOKIE:',// KEY 前缀
		'expire' => '86400',		// 过期时间一天
		'options' => array( 
			//'' => '',  // 其他REDIS 配置
		),
);


