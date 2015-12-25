<?php
/**
 * @file:  CSBankSoap.class.php
 * @brief:  长沙SOAP接口
 * @author:  Mark.Pan
 * @version:  0.1
 * @date:  2015-11-11
 */

class CSBankSoap
{

	protected static $client;


	/**
	 * @brief:  发送信息
	 * @return:  
	 */
	protected function sendQuery( $ServiceCode, $requestParms, $fetchAll=false )
	{
	    Log::bcsNotice( 'CSBankSoap===============>>sendQuery-str requestParms=##' . var_export( $requestParms, true ) . "##");
		$SendString = $this->getSendString( $ServiceCode, $requestParms );
		$client = $this-> getSoapClient();
		if( !$client ) {
		    Log::bcsError("getSoapClient failed ." );
		    return false;
		}
		Log::bcsNotice( 'CSBankSoap===============>>sendQuery request=##' . var_export( $SendString, true ) . "##");
		$resXMLString = $client->__soapCall( 'request', $SendString );
		Log::bcsNotice( 'CSBankSoap===============>>sendQuery response=##' . var_export( $resXMLString, true ) . "##");
		return $this->fetchArrayResult( $resXMLString, $fetchAll );
	}


	/**
	 * @brief:  从响应结果XML文件中，提取数组结果
	 * @param:  string $resXMLString  
	 * @param:  bool $fetchAll	是否获取所有结果信息数组, 默认只提取报文体BODY响应内容
	 * @return:  
	 */
	private function fetchArrayResult( $resXMLString, $fetchAll=false )
	{
		$arrResult = $this->xmlToArray( $resXMLString );
		if ( $fetchAll ) {
			return $arrResult;
		}
		$reData = [
			'code' => 0, 
			'msg' => '', 
			'data' => [] 
		];
		if ( $arrResult['Header']['Response']['ReturnMessage'] ) {
			$reData['code'] =  $arrResult['Header']['Response']['ReturnCode'];
			$reData['msg'] =  $arrResult['Header']['Response']['ReturnMessage'];
		}else{
			$reData['data'] = $arrResult['Body']['Response'];
		}
		return  $reData;
	}


	/**
	 * @brief:  获取要发送的报文信息
	 * @param:  string $ServiceCode
	 * @param:  array $requestParms
	 * @return:  string
	 */
	private function getSendString( $ServiceCode, $requestParms )
	{
		$bodyXmlString = $this-> constructBody( $requestParms );
		$headerXmlString = $this-> constructHeader( $ServiceCode, $bodyXmlString );
		return ["<Service>{$headerXmlString}{$bodyXmlString}</Service>"];
	}


	/**
	 * @brief:  获取SOAP客户端
	 * @return:  
	 */
	private function getSoapClient()
	{
		if ( !self::$client ) {
			$config = Controller::getConfig('conf');
			if( !$config['CSBankSoapUrl'] ) {
				Log::bcsError('conf/conf.ini.php  not  have "CSBankSoapUrl" ');
				return false;
			}
			try {
				self::$client = new SoapClient( $config['CSBankSoapUrl'] );
			} catch ( Exception $e ) {
				Log::bcsError( 'SOAP-ERROR: '. var_export( $e->getMessage(), true ) );
				return false;
			}
		}
		return self::$client;
	}


	/**
	 * @brief:  构造文本头
	 * @param: string $ServiceCode  服务编码 -- 见长沙银行接口Excel文档
	 * @param: array $bodyXmlString  接口需要是参数
	 * @param: 0 $RequestType  请求类型 0：正常 1：测试 2：重发
	 * @param: 0 $Encrypt 请求类型 0：正常 1：测试 2：重发
	 * @return:  
	 */
	private function constructHeader( $ServiceCode, $bodyXmlString, $RequestType='0', $Encrypt='0' )
	{
		$header = [];
// 		$header['ProductId'] = '';
		$header['ServiceCode'] = $ServiceCode;			// 服务编码
		$header['ChannelId'] = '607';					// 渠道号
		$header['ExternalReference'] = $this->getExternalReference(); // 渠道流水号
// 		$header['OriginalChannelId'] = '002';						  // 原渠道号  -- 目前照例子填的
// 		$header['OriginalReference'] = '201408210006485';	// 原渠道流水号 -- 目前也是乱填
		$header['RequestTime'] = date('YmdHis');			// 请求时间
		$header['TradeDate'] = substr( $header['RequestTime'], 0, 8 );  // 交易日期
		$header['Version'] = '1.0';										// 报文头版本  -- 照着例子写的
// 		$header['RequestBranchCode'] = 'CN0010001';		// 请求机构代号 -- 照例
// 		$header['RequestOperatorId'] = 'FB.ICOP.X01';	// 请求柜员代号
// 		$header['RequestOperatorType'] = '0';			// 请求柜员类型 0-实柜员 1-虚柜员
// 		$header['BankNoteBoxID'] = '';		// 柜员或是机具的钱箱号
// 		$header['AuthorizerID'] = '';		// 授权柜员号
		$header['TermType'] = '00000';		// 终端类型
		$header['TermNo'] = '0000000000';	// 终端号
		$header['RequestType'] = $RequestType; // 请求类型 0：正常 1：测试 2：重发
		$header['Encrypt'] = $Encrypt;		// 加密标志 0:明文 1:密文
		$header['SignData'] = $this->CreateSignData( $bodyXmlString ); // 签名数据
// 		var_dump($header['SignData']);
		return  $this->arrayToXml( ['Header'=>$header] );
	}


	/**
	 * @brief:  构造报文体
	 * @param:  $requestParms
	 * @return:  
	 */
	private  function constructBody( $requestParms )
	{
		$data = [
			'Body' => [
				'Request' => $requestParms
			],
		];
		return $this->arrayToXml( $data );
	}


	/**
	 * @brief:  加密报文体内容，获取认证签名，提过给报文头
	 * @param:  $body
	 * @return:  
	 */
	private function CreateSignData( $bodyXmlString )
	{
		$privateKeyFilePath = './security/008.08.pfx';
		$privKeyPassword = '952789';
		return  $this->sign($bodyXmlString, $privateKeyFilePath, $privKeyPassword);
	}

	/**
	 * @brief:  获取流水号，渠道流水号
	 * @return:  
	 */
	protected function getExternalReference()
	{
		$d = explode( '.', microtime(true) );
		// 规则：  年月日时分秒4位微秒3位随机数
		return date( 'YmdHis' ) . str_pad( $d[1], 4, 0, STR_PAD_LEFT) . str_pad( mt_rand(1, 999), 3, 0, STR_PAD_LEFT);
	}


	/**
	 * @brief:  XML 返回结果 转数组
	 * @param:  $xml
	 * @return:  
	 */
	private function xmlToArray( $xml )
	{
		return json_decode( json_encode( simplexml_load_string( $xml ) ), true );
	}

	
	/**
	 * @brief:  参数数组装成XML
	 * @param:  $arr
	 * @return:  
	 */
	private function arrayToXml( $arr )
	{
		$xml = '';
		foreach ( $arr as $k=>$v )
		{
			if ( $v === '' ) 
			{
				$xml .= "<{$k}/>";
			}
			else
			{
				$xml .= "<{$k}>";
				if ( is_array( $v ) ) {
					$xml .= $this-> arrayToXml( $v );
				} else {
					$xml .= "{$v}";
				}
				$xml .= "</{$k}>";
			}
		}
		return $xml;
	}
	




	/**
	 * @brief:  读取签证，
	 * @param:  $data  加密原文
	 * @param:  $privatekeyFile  私钥文件路径   .pfx 文件
	 * @param:  $privKeyPassword  私钥密码
	 * @return:  
	 */
	private function sign( $data, $privateKeyFilePath, $privKeyPassword )  
	{
		$certs = [];
		$signMsg = '';
		// 用私钥密码，解码读取.pfx私钥证书，获取私钥
		openssl_pkcs12_read( file_get_contents($privateKeyFilePath), $certs, $privKeyPassword);
		$prikeyid = $certs['pkey']; //私钥  
		openssl_sign($data, $signMsg, $prikeyid, OPENSSL_ALGO_SHA1); // 私钥加密
		return  strtoupper(bin2hex($signMsg)); // 转大写( 必须 )
	}


	/**
	 * @brief:  验证原签名
	 * @param:  $data		原来数据
	 * @param:  $signature  签名数据
	 * @param:  $publicKeyFilePath 公钥文件路径  .cer 文件
	 * @return:  
	 */
	private function verity($data, $signature, $publicKeyFilePath)  
	{  
		return openssl_verify( $data, hex2bin( $signData ), file_get_contents( $publicKeyFilePath ) ); 
	}

}

