<?php
/**
 * sms class file
 *
 * sms操作类 
 * @version $Id: sms.class.php
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')){
    exit();
}

class sms extends Base{  

    public static function get_proxy($proxy_ip = '',$proxy_port = 8001,$check_url = '',$time_out=30,$retry=2) {
        //获取代理IP
        $conf = Controller::getConfig('conf');
        $proxy_ip = $conf['proxy_ip'];
        $proxy_port = $conf['proxy_port'];
        // 创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $check_url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time_out);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if(!empty($proxy_ip) && !empty($proxy_port)){              
            curl_setopt($ch, CURLOPT_PROXY, $proxy_ip.':'.$proxy_port);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // 抓取URL并把它传递给浏览器
       /* $i = 1;
        $result = false;
        while($i <= $retry) {
            $result = curl_exec($ch);
            if( $result !== false && substr_count($result, 'User-agent: Baiduspider') >=1 ) {
                $result = true;
                break;
            } else {
                $result = false;
            }
            ++$i;
        }*/
        $result = curl_exec($ch);
         
        // 关闭cURL资源，并且释放系统资源
        curl_close($ch);
        //成功返回boolean true, 失败返回boolean false
        return $result;
    }
     /**
     * ims选号
     *
     *@param string $MethodName 
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
    */

     public function selectNum($root_url='http://110.84.128.78:8088',$MethodName='IMS_QUERY',$Spid,$Appid,$Passwd){
        //参数分析
        if (is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Passwd)){
            return false;
        }  

        $Passwd = sha1($Passwd);
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$Passwd."</Passwd></Head><Body></Body></Request>";
        //获取接口返回数据
        Log::notice('select num req:' . $url);
        //$querst = file_get_contents($url);   
        $querst = self::get_proxy('',8001,$url,30);      
        Log::notice('select num res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组  
        if($data['Head']['Result']=='0'){
            return $data['Body']['Data'];
        }else{
            return false;
        }

     }

     /**
     * 号码预占
     *
     *@param string $MethodName 
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
     *@param string $Ims 计费号码（发送号码）
     *@param string $Key $Ims密钥
    */

     public function numCampon($root_url='http://110.84.128.78:8088',$MethodName='IMS_OCCUPY',$Spid,$Appid,$Passwd,$Ims,$Key){
        //参数分析
        if (is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Passwd) || is_null($Ims) || is_null($Key)){
            return false;
        }  

        $Passwd = sha1($Passwd);
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$Passwd."</Passwd></Head><Body><Ims>".$Ims."</Ims><Key>".$Key."</Key></Body></Request>";
        //获取接口返回数据
        Log::notice('register num req:' . $url);
       // $querst = file_get_contents($url); 
        $querst = self::get_proxy('',8001,$url,30);
        Log::notice('register num res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组  
        if($data['Head']['Result']=='0'){
            return true;
        }else{
            return false;
        }

     }


     /**
     * 号码获取验证码
     *
     *@param string $MethodName 
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
    */

     public function numGetCode($root_url='http://110.84.128.78:8088',$MethodName='IMS_SMS',$Spid,$Appid,$Passwd,$Type='1',$Telno){
        //参数分析
        if (is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Passwd) || is_null($Telno)){
            return false;
        }  

        $Passwd = sha1($Passwd);
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$Passwd."</Passwd></Head><Body><Type>".$Type."</Type><Telno>".$Telno."</Telno></Body></Request>";
        //获取接口返回数据
        Log::notice('Campon num req:' . $url);
        //$querst = file_get_contents($url); 
        $querst = self::get_proxy('',8001,$url,30);
        Log::notice('Campon num res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组  
        if($data['Head']['Result']=='0'){
            return true;
        }else{
            return false;
        }

     }

     /**
     * 号码获取验证码
     *
     *@param string $MethodName 
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
    */

     public function numRegister($root_url='http://110.84.128.78:8088',$MethodName='IMS_SMS',$Spid,$Appid,$Passwd,$Type='1_1',$Telno,$Code,$Ims,$Key){
        //参数分析
        if (is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Passwd) || is_null($Telno) || is_null($Code) || is_null($Ims) || is_null($Key)){
            return false;
        }  

        $Passwd = sha1($Passwd);
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$Passwd."</Passwd></Head><Body><Type>".$Type."</Type><Telno>".$Telno."</Telno><Code>".$Code."</Code><Ims>".$Ims."</Ims><Key>".$Key."</Key></Body></Request>";
        //获取接口返回数据
        Log::notice('Campon num req:' . $url);
        //$querst = file_get_contents($url); 
        $querst = self::get_proxy('',8001,$url,30);
        Log::notice('Campon num res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组  
        if($data['Head']['Result']=='0'){
            return true;
        }else{
            return false;
        }

     }


    /**
     * sms 短信签名
     *
     *@param string $SmsSign 
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
     *@param string $Ims 计费号码（发送号码）
     *@param string $Key $Ims密钥
     *@param string $Sign 签名
     *@return $Appid
    */
    public function sign($root_url=null,$SmsSign='SmsSign',$Spid=null,$Appid=null,$password=null,$Ims=null,$Key=null,$Sign='大汉电商'){
        //参数分析
        if (is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Ims) || is_null($Key) || is_null($password)){
            return false;
        }       
        $password = sha1($password);
        //获取数据接口
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$SmsSign."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$password."</Passwd></Head><Body><Ims>".$Ims."</Ims><Key>".$Key."</Key><Sign>".$Sign."</Sign></Body></Request>";
        //获取接口返回数据
        Log::notice('sign sms req:' . $url);
        //$querst = file_get_contents($url); 
        $querst = self::get_proxy('',8001,$url,30);
        Log::notice('sign sms res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组  
        if($data['Head']['Result']=='0'){
            return true;
        }else{
            return false;
        }
        //return array('code'=>$data['Head']['Result'],'msg'=>$data['Head']['ResultDesc']);//code=0表示成功code=-1表示失败    
        //return $data['Head']['Result'];//返回0表示成功-1表示失败    

    }   

     /**
     * sms 短信发送
     *
     *@param string $MethodName 方式名称
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
     *@param string $Ims 计费号码（发送号码）
     *@param string $Key $Ims密钥
     *@param string $phonenum 要发送短信到的手机或电话的号码
     *@return int $modelId 发送信息的模板id
     *@return string $Val1-6 一些参数数据根据$modelId个值不一样
    */
    public function send($root_url='http://110.84.128.78:8088',$MethodName='SmsSendByTemplet',$Spid=null,$Appid=null,$password=null,$Ims=null,$Key=null,$phonenum=null,$modelId=null,$Val1=null,$Val2=null,$Val3=null,$Val4=null,$Val5=null,$Val6=null){
        //参数分析          
        if(is_null($root_url) || is_null($Spid) || is_null($Appid) || is_null($Ims) || is_null($Key) || is_null($modelId) || is_null($password) || is_null($phonenum) || is_null($Val1) || is_null($Val2)){
             return false;
        }
        $password = sha1($password);
        //获取数据接口
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$password."</Passwd></Head><Body><Ims>".$Ims."</Ims><Key>".$Key."</Key><CalleeNbr>".$phonenum."</CalleeNbr><TempletId>".$modelId."</TempletId><Value1>".$Val1."</Value1><Value2>".$Val2."</Value2><Value3>".$Val3."</Value3><Value4>".$Val4."</Value4><Value5>".$Val5."</Value5><Value6>".$Val6."</Value6></Body></Request>";
        //获取接口返回数据
        Log::notice('send sms req:' . $url);
        //$querst = file_get_contents($url); 
        $querst = self::get_proxy('',8001,$url,30);
        Log::notice('send sms res:' . $querst);
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组 
        return $data['Head']['Result'] == 0 ? true : false;
        //return $data['Head']['Result'];//返回0表示成功-1表示失败
    }  


    /**
     * sms 点击拨号
     *
     *@param string $MethodName 方式名称
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
     *@param string $ChargeNbr 绑定号码
     *@param string $Key Ims密钥
     *@param string $DisplayNbr 显示号码（默认绑定号码）
     *@return int $CallerNbr 主叫号码
     *@return string $CallerNbr 被叫号码
    */
    public function call($root_url=null,$MethodName='Dial',$Spid=null,$Appid=null,$password=null,$ChargeNbr=null,$Key=null,$DisplayNbr=null,$CallerNbr=null,$CalleeNbr=null,$Record=null){
        //参数分析       
        if(!isset($root_url) || !isset($Spid) || !isset($Appid) || !isset($ChargeNbr) || !isset($Key) || !isset($password) || !isset($CallerNbr) || !isset($CalleeNbr)){
             return false;
        }
        $password = sha1($password);
        //获取数据接口
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$password."</Passwd></Head><Body><ChargeNbr>".$ChargeNbr."</ChargeNbr><Key>".$Key."</Key><DisplayNbr>".$DisplayNbr."</DisplayNbr><CallerNbr>".$CallerNbr."</CallerNbr><CalleeNbr>".$CalleeNbr."</CalleeNbr><Record>".$Record."</Record></Body></Request>";
        //获取接口返回数据
       // $querst = file_get_contents($url);
       $querst = self::get_proxy('',8001,$url,30);  
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组    
        if($data['Head']['Result']==0){ //成功       
            return true;
        }else{
            return false;
        }
    }

    /**
     * sms 拨号中断
     *
     *@param string $MethodName 方式名称
     *@param int $Spid 注册sp时获取spid
     *@param int $Appid 创建应用时分配
     *@param string $password 登陆密码
     *@param string $Sessionid 会话sessionid
    */
    public function brack_call($root_url=null,$MethodName='DialStop',$Spid=null,$Appid=null,$password=null,$Sessionid=null){
        //参数分析       
        if(!isset($root_url) || !isset($Spid) || !isset($Appid) || !isset($password) || !isset($Sessionid)){
             return false;
        }
        $password = sha1($password);
        //获取数据接口
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$password."</Passwd></Head><Body><Sessionid>".$Sessionid."</Sessionid></Body></Request>";
        //获取接口返回数据
        //$querst = file_get_contents($url);  
        $querst = self::get_proxy('',8001,$url,30);//10.44.82.155  
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组   
        if($data['Head']['Result']==0){ //成功       
            return true;
        }else{//失败
            return false;
        }          
    }     

    /**
     * sms 套餐购买
    */
    public function buy($root_url=null,$MethodName='IMS_BUY',$Spid=null,$Appid=null,$password=null,$PointsSpID=null,$Ims=null,$Key=null,$Type=1,$EffectType=1){
        //参数分析       
        if(!isset($root_url) || !isset($Spid) || !isset($Appid) || !isset($password) || !isset($PointsSpID) || !isset($Ims) || !isset($Key)){
             return false;
        }
        $password = sha1($password);
        //获取数据接口
        $url = $root_url."/httpIntf/dealIntf?postData=<Request><Head><MethodName>".$MethodName."</MethodName><Spid>".$Spid."</Spid><Appid>".$Appid."</Appid><Passwd>".$password."</Passwd></Head><Body><PointsSpId>".$PointsSpID."</PointsSpId><Ims>".$Ims."</Ims><Key>".$Key."</Key><Type>".$Type."</Type><EffectType>".$EffectType."</EffectType></Body></Request>";
        //获取接口返回数据
        //$querst = file_get_contents($url);  
        $querst = self::get_proxy('',8001,$url,30);//10.44.82.155  
        //处理返回结果 
        $data = json_decode(json_encode(simplexml_load_string($querst)),true);//得到一个数组   
        if($data['Head']['Result']==0){ //成功       
            return true;
        }else{//失败
            return false;
        }          

    }
   
}
