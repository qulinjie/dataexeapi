<?php
class TestController extends BaseController
{
	protected static $client;

    public function handle( $params=[] )
    {
		if ( !$params ) {
			$this->laiyifa();

		} else 
        switch( $params[0] )
        {
            case 'testSaop1':
                $this-> testSaop1();
                break;
            case 'testSaop2':
                $this-> testSaop2();
                break;
            default:
                Log::error('page not found');
                EC::page_not_found();
                break;
        }
    }

	public function laiyifa()
	{
		//  长沙银行接口MODEL
		$BankModel = $this->model( 'Bank' );


		/* ===================== 测试获取商户信息
		$res = $BankModel->getMarketInfo( 198209 ); // 测试获取商户信息
		var_dump($res);
		return '';
		 */




		/* ===================== 测试获取商户信息
		$res = $BankModel->getMarketInfo( 198209 ); // 测试获取商户信息
		var_dump($res);
		return '';
		 */

		/* ================================= 用户注册通知
		$requestParms = []; 
		$requestParms['MCH_NO'] = '198209';					// 商户编号
		$requestParms['CUST_CERT_TYPE'] = '21';			// 客户证件类型
		$requestParms['CUST_CERT_NO'] = '9800008107';				// 客户证件号码 
		$requestParms['SIT_NO'] = 'DDMG00007';					// 席位号
		$requestParms['CUST_NAME'] = '湖南省领导人才资源开发中心';				// 客户名称
		$requestParms['CUST_ACCT_NAME'] = '湖南省领导人才资源开发中心';			// 客户账户名
		$requestParms['CUST_SPE_ACCT_NO'] = '800052170901011';			// 客户结算账户
		$requestParms['CUST_SPE_ACCT_BKTYPE'] = '0';	// 客户结算账户行别  0-长沙银行；1-非长沙银行
			//$requestParms['CUST_SPE_ACCT_BKID'] = '6214836558162364';	// 客户结算账户行号
			//$requestParms['CUST_SPE_ACCT_BKNAME'] = '招商银行';	// 客户结算账户行名
	$requestParms['ENABLE_ECDS'] = '1';				// 是否开通电票
	$requestParms['IS_PERSON'] = '0';				// 是否个人
		$res = $BankModel->registerCustomer($requestParms);
		var_dump($res);
		 */



	}

    
    public function testSaop1(){
        $model = Controller::instance( 'ErpSoap' );
        $data = $model->testForAddOrder();
        Log::error('TestController->testSaop1. data=' . $data);
        EC::success(EC_OK,$data);
    }
    
    public function testSaop2(){
        $model = Controller::instance( 'ErpSoap' );
        $data = $model->getCompanyList("http://124.232.142.207:8080/DhErpService/services/erpservice?wsdl","大汉物流股份有限公司");
        Log::error('TestController->testSaop2. data=' . $data);
        EC::success(EC_OK,$data);
    }

	public function test()
	{

		//$JianCai = ['px'=>'普线', 'plzt'=>'盘螺直条', 'xzlw'=>'校直螺纹', 'zgx'=>'准高线', 'gxzt'=>'高线直条', 'jzlwg'=>'精轧螺纹钢', 'jzlm'=>'精轧螺帽', 'llx'=>'冷拉线' ];
		//$youGang = [ 'py'=>'普圆', 'ty'=>'碳圆' ];
		//$banCai = [ 'gqg'=>'高强钢', 'xxgb'=>'新兴割板', 'nhb'=>'耐厚板', 'nsb'=>'耐酸板', 'ljb'=>'冷卷板', 'rjb'=>'热卷板', 'mbb'=>'毛边板', 'nmb'=>'耐磨板', 'bjb'=>'包角板', 'lzdlg'=>'冷轧带肋钢', 'rzdlg'=>'热轧带肋钢', 'yxw'=>'压型瓦', 'blw'=>'玻璃瓦', 'cgw'=>'彩钢瓦', 'db'=>'垫板' ];
		//$guangCai = [ 'fg'=>'方管', 'wfgg'=>'无缝钢管', 'zfgg'=>'直缝钢管', 'jxg'=>'矩形管' ];
		//$xingCai = [ 'zfgg'=>'直缝钢管' ];
		//$juancai = [ 'lj'=>'冷卷', 'cg'=>'彩卷', 'dgg'=>'电工钢' ];
		die( '测试接口' );
		return false;

		$count = 0;
		//foreach ( $JianCai as $k=>$v )
		//foreach ( $youGang as $k=>$v )
		foreach ( $juancai as $k=>$v )
		{
			$data = [];
			$data['id'] = $this->model( 'id' )->getProductId();
			if ( !$data['id'] ) {
				echo $val;
				exit;
			}
			$data['short_name'] = $k;
			$data['name'] = $v;
			$data['category_id'] = 12;
			$data['technic_id'] = '0';
			$data['sort'] = '9999';
			$info = $this-> model( 'product' )->createProduct($data);
			$count++;
		}
		var_dump(count( $juancai ));
		var_dump($count);

	return false;

		$nohave = [];
		$have = [];
		foreach ( $category as $k => $c ) {
			$info = $this-> model( 'product' )->getProduct( 'name like "%'.$c.'%"' );
			//$info = $this-> model( 'product' )->getProduct( 'name = "'.$c.'"' );
			if ( !$info ) {
				$nohave[] = $c;
			}else{
				$have[] = $c;
			}
		}

		echo '<pre>';
		//print_r($nohave);
		print_r($have);
		echo '</pre>';
	}
    
	public function testGet()
	{
		$ServiceCode = 'FMSCUST0002';
		$requestParms = ['MCH_NO'=>'198209'];
		//var_dump($ServiceCode);
		//$ServiceCode = 'FMSCUST0003';
		//$requestParms = ['MCH_NO'=>'198209', 'SIT_NO'=>'1'];
		$res = $this-> sendQuery( $ServiceCode, $requestParms, $fetchAll=false );
		var_dump($res);
	}

	public function markTest()
	{
		$url = 'http://162.16.1.137:43294/icop/services/JTService?wsdl';
		//$url = 'http://120.25.1.102/ddmg_pay/services/JTService.php?wsdl';
		//$url = 'http://124.232.142.207:8080/DhErpService/services/erpservice?wsdl';
		$clien = new SoapClient( $url );
		$xmlStr = '<Service>  
					   <Header>   
						 <ProductId/> 
							<ServiceCode>FMSCUST0002</ServiceCode> 
							<ChannelId>607</ChannelId> 
							<ExternalReference>370000201408210006485</ExternalReference>
							<OriginalChannelId>002</OriginalChannelId>
							<OriginalReference>201408210006485</OriginalReference>
							<RequestTime>20150801110925</RequestTime>
							<TradeDate>20150518</TradeDate>
							<Version>1.0</Version>
							<RequestBranchCode>CN0010001</RequestBranchCode>
							<RequestOperatorId>FB.ICOP.X01</RequestOperatorId>
							<RequestOperatorType>0</RequestOperatorType>
							<TermType>00000</TermType>
							<TermNo>0000000000</TermNo>
							<RequestType>0</RequestType>
							<Encrypt>0</Encrypt>
					 </Header>   
					 <Body> 
						  <Request>   
						 <MCH_NO>198209</MCH_NO>   
						 </Request> 
					   </Body>
				 </Service>';

		/*
		$resArr = $this->xmlToArray( $xmlStr );
		$resXML = $this->arrayToXml( $resArr );
		var_dump($resArr);
		var_dump($this->xmlToArray($resXML));
		 */
		

		//var_dump(substr($this-> getExternalReference(), 0, 14));
		/*
		 *$data = [
		 *    'Service'=>[
		 *        'Header'=>[
		 *            'ProductId' => '', 
		 *            'ServiceCode' => 'FMSCUST0002', 
		 *            'ChannelId' => '607', 
		 *        ], 
		 *        'Body'=>[
		 *            'Request' => [
		 *                'MCH_NO' => '198209'
		 *            ], 
		 *        ], 
		 *    ]
		 *];
		 *$data = [
		 *        'MCH_NO'=>'198209', 
		 *        'MCH_Ne'=>'198209', 
		 *];
		 */
		//var_dump($data);
		//var_dump( $this->arrayToXml( $data ) );
		//var_dump( $this->constructBody( $data ) );

		$res = $clien->__soapCall( 'request', [$xmlStr] );
		$resArr = $this->xmlToArray( $res );
		var_dump($resArr);

		//new SoapVal(  );
		//var_dump($clien->__getFunctions());
		//var_dump($clien->__getTypes());
	}


	/**
	 * @brief:  发送信息
	 * @return:  
	 */
	public function sendQuery( $ServiceCode, $requestParms, $fetchAll=false )
	{
		$SendString = $this->getSendString( $ServiceCode, $requestParms );
		$client = $this-> getSoapClient();
		$resXMLString = $client->__soapCall( 'request', $SendString );
		return $this->fetchArrayResult( $resXMLString, $fetchAll );
		//return $this-> xmlToArray( $resXML );
	}

	private function fetchArrayResult( $resXMLString, $fetchAll=false )
	{
		$arrResult = $this->xmlToArray( $resXMLString );
		if ( $fetchAll ) {
			return $arrResult;
		}
		return $arrResult['Body']['Response'];
	}

	private function getSendString( $ServiceCode, $requestParms )
	{
		$bodyXmlString = $this-> constructBody( $requestParms );
		$headerXmlString = $this-> constructHeader( $ServiceCode, $bodyXmlString );
		return ["<Service>{$headerXmlString}{$bodyXmlString}</Service>"];
	}

	private function getSoapClient()
	{
		if ( !self::$client ) {
			$soapApiUrl = 'http://162.16.1.137:43294/icop/services/JTService?wsdl';
			self::$client = new SoapClient( $soapApiUrl );
		}
		return self::$client;
	}

	private function constructHeader( $ServiceCode, $bodyXmlString, $RequestType='0', $Encrypt='0' )
	{
		$header = [];
		$header['ProductId'] = '';
		$header['ServiceCode'] = $ServiceCode; // 服务编码
		$header['ChannelId'] = '607';	// 渠道号
		$header['ExternalReference'] = $this->getExternalReference(); // 渠道流水号
		$header['OriginalChannelId'] = '002'; // 原渠道号  -- 目前照例子填的
		$header['OriginalReference'] = '201408210006485'; // 原渠道流水号 -- 目前也是乱填
		$header['RequestTime'] = date('YmdHis'); // 请求时间
		$header['TradeDate'] = substr( $header['RequestTime'], 0, 8 ); // 交易日期
		$header['Version'] = '1.0'; // 报文头版本  -- 照着例子写的
		$header['RequestBranchCode'] = 'CN0010001'; // 请求机构代号 -- 照例
		$header['RequestOperatorId'] = 'FB.ICOP.X01'; // 请求柜员代号
		$header['RequestOperatorType'] = '0'; // 请求柜员类型 0-实柜员 1-虚柜员
		//$header['BankNoteBoxID'] = $a; // 柜员或是机具的钱箱号
		//$header['AuthorizerID'] = $a; // 授权柜员号
		$header['TermType'] = '00000'; // 终端类型
		$header['TermNo'] = '0000000000'; // 终端号
		$header['RequestType'] = $RequestType; // 请求类型 0：正常 1：测试 2：重发
		$header['Encrypt'] = $Encrypt; // 加密标志 0:明文 1:密文
		$header['SignData'] = $this->CreateSignData( $bodyXmlString ); // 签名数据
		return  $this->arrayToXml( ['Header'=>$header] );
	}

	private  function constructBody( $requestParms )
	{
		$data = [
			'Body' => [
				'Request' => $requestParms
			],
		];
		return $this->arrayToXml( $data );
	}

	private function CreateSignData( $body )
	{
		return ''; // 数字签名加密
	}

	/**
	 * @brief:  获取流水号，渠道流水号
	 * @return:  
	 */
	protected function getExternalReference()
	{
		$d = explode( '.', microtime(true) );
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

}
