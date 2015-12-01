<?php
/**
 * @file:  erp_service.ini.php
 * @brief:  用友ERP，SOAP接口URL地址配置
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-11-26
 */
return [
	'soapUrl' => 'http://124.232.142.207:8080/DhErpService/services/erpservice?wsdl',  // 正式接口地址
	'soapUrlTest' => 'http://220.168.65.186:8980/DhErpService/services/erpservice?wsdl',  // 测试接口地址
	//'proxy_host' => '10.44.82.155', // 线上环境，内网代理地址
	//'proxy_port' => '3128', // 线上环境内网代理端口
];
