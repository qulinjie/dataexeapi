<?php
/**
 * ERP接口访问类 
 * 
 * @author zhangkui
 * @version 1.0
 * @package core
 * 
 */
class ErpSoap extends Base
{
    private static $soapClient = null;

	public static function getSoapClient($url='')
	{
        if(null != self::$soapClient) {
            return self::$soapClient;
        }
        self::$soapClient = ErpSoap::createSoapClient($url);
        return ErpSoap::$soapClient;
    }
    
    /**
     * 查询库存列表
     * 
     * @param string $url
     * @param string $p_warehousePK
     * @param string $p_companyPK
     * @param string $p_categoryPK
     * @param string $p_name
     * @param string $p_spec
     * @param string $p_model
     * @param string $p_brand
     * @param string $p_greatZero
     * @return data_array(entry_array(attribute))
     */
	public static function getStoreList($url = '' ,$p_warehousePK = '' ,$p_companyPK = '' ,$p_categoryPK = 'all' ,$p_name = 'all' ,$p_spec = 'all' ,$p_model = 'all' ,$p_brand = 'all' ,$p_greatZero = true)
	{
        $param = array('in0'=>$p_warehousePK,'in1'=>$p_companyPK,'in2'=>$p_categoryPK,'in3'=>$p_name,'in4'=>$p_spec,'in5'=>$p_model,'in6'=>$p_brand,'in7'=>$p_greatZero);
		$sClient = ErpSoap::getSoapClient($url);
		$response = $sClient->GetStroeList($param);
        //$sClient = ErpSoap::getSoapClient($url);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out']['Store'];
        return $data;
    }
    
    /**
     * 查询公司列表
     * 
     * @param string $url
     * @param string $p_companyName
     * @return data_array(entry_array(attribute))
     */
	public static function getCompanyList( $url = '', $p_companyName = '' )
	{
        $param = array('in0'=>$p_companyName);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetCompanyList($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out']['Company'];
        return $data;
    }
    
    /**
     * 查询客商列表
     * 
     * @param string $url
     * @param string $p_customerName
     * @return data_array(entry_array(attribute))
     */
	public static function getCustomerList( $url = '', $p_customerName = '' )
	{
        $param = array('in0'=>$p_customerName);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetCustomerList($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out']['Customer'];
        return $data;
    }
    
    /**
     * 查询仓库列表
     * 
     * @param string $url
     * @param string $p_companyPK
     * @return data_array(entry_array(attribute))
     */
    public static function getWarehouseList($url = '' ,$p_companyPK = ''){
        $param = array('in0'=>$p_companyPK);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetWarehouseList($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out']['Warehouse'];
        return $data;
    }
    
    /**
     * 查询仓库
     *
     * @param string $url
     * @param string $p_warehousePK
     * @return entry_array(attribute)
     */
    public static function getWarehouseByPk($url = '' ,$p_warehousePK = ''){
        $param = array('in0'=>$p_warehousePK);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetWarehouseByPk($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out'];
        return $data;
    }
    
    /**
     * 查询存货分类
     * 
     * @param string $url
     * @param string $p_categoryPK
     * @return entry_array(attribute)
     */
    public static function getCategory($url = '' ,$p_categoryPK = ''){
        $param = array('in0'=>$p_categoryPK);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetCategory($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out'];
        return $data;
    }
    
    /**
     * 查询存货分类列表
     * 
     * @param string $url
     * @param string $p_parentCode
     * @return data_array(entry_array(attribute))
     */
    public static function getCategoryList($url = '' ,$p_parentCode = ''){
        $param = array('in0'=>$p_parentCode);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->GetCategoryList($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out']['Category'];
        return $data;
    }
    
    /**
     * 增加订单
     * 
     * @param string $url
     * @param array $p_orderArray
     * @return array(error,result,value)
     */
    public static function addOrder($url = '' ,$p_orderArray = array()){
        $param = array('in0'=>$p_orderArray);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->AddOrder($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out'];
        return $data;
    }
    
    /**
     * 取消订单
     * 
     * @param string $url
     * @param string $p_orderPK
     * @return array(error,result,value)
     */
    public static function cacelOrder($url = '' ,$p_orderPK= ''){
        $param = array('in0'=>$p_orderPK);
        $sClient = ErpSoap::getSoapClient($url);
        $response = $sClient->CacelOrder($param);
        $arr = ErpSoap::objectToArray($response);
        $data = $arr['out'];
        return $data;
    }
    
    /**
     * 测试 增加订单 接口 【测试环境】
     * @return array(error,result,value)
     */
    public static function testForAddOrder(){
        $url = 'http://220.168.65.186:8980/DhErpService/services/erpservice?wsdl';
        
        $p_orderArray = array();
        
        $p_orderArray['IDCode'] = '431121200006206018';
        $p_orderArray['LPN'] = '湘A9527';
        $p_orderArray['address'] = '长沙市湘江新区普瑞大道西';
        $p_orderArray['code'] = 'V0003';
        $p_orderArray['companyPK'] = '1006'; // 湖南大强钢铁贸易有限公司
        $p_orderArray['customerPK'] = '长沙市洺顺钢材贸易有限公司'; // 1006A110000000001ZMO
        
        $orderItem = array();
        $orderItem['invbasdocPK'] = '0001A11000000000LEOO';
        $orderItem['otherMoney'] = '90';
        $orderItem['price'] = '2350';
        $orderItem['qty'] = '35';
        $orderItem['storePK'] = '1006A6100000000002BK'; // 大汉博远库
        $orderItem['taxRate'] = '10';
        $orderItem['weight'] = '10.56';
        
        $p_orderArray['items'] = $orderItem;
        
        $p_orderArray['mobile'] = '13265431549';
        $p_orderArray['person'] = '李四';
        $p_orderArray['recMobile1'] = '13265431549';
        $p_orderArray['recMobile2'] = '13265431549';
        $p_orderArray['recPerson'] = '张三';
        $p_orderArray['receipt'] = '长沙市洺顺钢材贸易有限公司';
        $p_orderArray['receiptMode'] = '0'; // 不需要发票
        $p_orderArray['transMode'] = '货到付款';
        $p_orderArray['warehousePK'] = '1006A6100000000002BK';
        $p_orderArray['zipCode'] = '100010';
        
        $data = self::addOrder($url,$p_orderArray);
        Log::error('response_data ==========> ##' . var_export($data, true) . '##');
        return $data;
    }
    
    public static function createSoapClient($url) {
        header('Content-Type: text/html; charset=utf-8');

		$conf = Controller::getConfig('erp_service');
		if ( !$conf['proxy_host'] ) {
			Log::error( ' erp_service.ini.php proxy_host not get ' );
		}
		if ( !$conf['proxy_port'] ) {
			Log::error( ' erp_service.ini.php proxy_port not get ' );
		}
        $sClient = new SoapClient($url, array( 'proxy_host'=>$conf['proxy_host'], 'proxy_port'=>$conf['proxy_port'] ));
        $strHeaderComponent_Session = "<AuthenticationToken><Username>gh</Username><Password>gh</Password></AuthenticationToken>";
        $objVar_Session_Inside = new SoapVar($strHeaderComponent_Session, XSD_ANYXML, null, null, null);
        $objHeader_Session_Outside = new SoapHeader('http://erp.dh', 'AuthenticationToken', $objVar_Session_Inside);
        $sClient->__setSoapHeaders(array($objHeader_Session_Outside));
        return $sClient;
    }
    
    public static function objectToArray($array)
    {
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value)
            {
                $array[$key] = ErpSoap::objectToArray($value);
            }
        }
        return $array;
    }
    
}

