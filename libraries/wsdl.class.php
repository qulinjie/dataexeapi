<?php
/**
 * wsdl class file
 *
 * 注:本类代码来自YII framework 1.1.5, 略有改动
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: wsdl.class.php 1.3 2011-11-13 21:21:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class wsdl extends Base {

    /**
     * @var string encoding of the Web service. Defaults to 'UTF-8'.
     */
    public $encoding='UTF-8';

    /**
     * @var string the namespace to be used in the generated WSDL.
     * If not set, it defaults to the name of the class that WSDL is generated upon.
     */
    public $namespace;
    /**
     * @var string the name of the generated WSDL.
     * If not set, it defaults to "urn:{$className}wsdl".
     */
    public $serviceName;

    private $_operations;
    private $_types;
    private $_messages;

    public $providerClass;
    public $serviceUrl;


    /**
     * 构造函数
     *
     * @param string $providerClass
     * @param string $serviceUrl
     * @return boolean
     */
    public function __construct($providerClass, $serviceUrl) {

        $this->providerClass = $providerClass;
        $this->serviceUrl    = $serviceUrl;

        return true;
    }

    /**
     * Generates the WSDL for the given class.
     * @param string $className class name
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     * @return string the generated WSDL
     */
    public function createWsdl($className, $serviceUrl, $encoding='UTF-8')
    {
        $this->_operations=array();
        $this->_types=array();
        $this->_messages=array();
        if($this->serviceName===null)
            $this->serviceName=$className;
        if($this->namespace===null)
            $this->namespace="urn:{$className}wsdl";

        $reflection=new ReflectionClass($className);
        foreach($reflection->getMethods() as $method)
        {
            if($method->isPublic())
                $this->processMethod($method);
        }

        return $this->buildDOM($serviceUrl,$encoding)->saveXML();
    }

    /*
     * @param ReflectionMethod $method method
     */
    private function processMethod($method)
    {
        $comment=$method->getDocComment();
        if(strpos($comment,'@soap')===false)
            return;
        $methodName=$method->getName();
        $comment=preg_replace('/^\s*\**(\s*?$|\s*)/m','',$comment);
        $params=$method->getParameters();
        $message=array();
        $n=preg_match_all('/^@param\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches);
        if($n>count($params))
            $n=count($params);
        for($i=0;$i<$n;++$i)
            $message[$params[$i]->getName()]=array($this->processType($matches[1][$i]), trim($matches[3][$i])); // name => type, doc

        $this->_messages[$methodName.'Request']=$message;

        if(preg_match('/^@return\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches))
            $return=array($this->processType($matches[1]),trim($matches[2])); // type, doc
        else
            $return=null;
        $this->_messages[$methodName.'Response']=array('return'=>$return);

        if(preg_match('/^\/\*+\s*([^@]*?)\n@/s',$comment,$matches))
            $doc=trim($matches[1]);
        else
            $doc='';
        $this->_operations[$methodName]=$doc;
    }

    /*
     * @param string $type PHP variable type
     */
    private function processType($type)
    {
        static $typeMap=array(
            'string'  =>'xsd:string',
            'str'     =>'xsd:string',
            'int'     =>'xsd:int',
            'integer' =>'xsd:integer',
            'float'   =>'xsd:float',
            'double'  =>'xsd:float',
            'bool'    =>'xsd:boolean',
            'boolean' =>'xsd:boolean',
            'date'    =>'xsd:date',
            'time'    =>'xsd:time',
            'datetime'=>'xsd:dateTime',
            'array'   =>'soap-enc:Array',
            'object'  =>'xsd:struct',
            'mixed'   =>'xsd:anyType',
        );
        if(isset($typeMap[$type]))
            return $typeMap[$type];
        else if(isset($this->_types[$type]))
            return is_array($this->_types[$type]) ? 'tns:'.$type : $this->_types[$type];
        else if(($pos=strpos($type,'[]'))!==false) // if it is an array
        {
            $type=substr($type,0,$pos);
            if(isset($typeMap[$type]))
                $this->_types[$type.'[]']='xsd:'.$type.'Array';
            else
            {
                $this->_types[$type.'[]']='tns:'.$type.'Array';
                $this->processType($type);
            }
            return $this->_types[$type.'[]'];
        }
    }

    /*
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     */
    private function buildDOM($serviceUrl,$encoding)
    {
        $xml="<?xml version=\"1.0\" encoding=\"$encoding\"?>
<definitions name=\"{$this->serviceName}\" targetNamespace=\"{$this->namespace}\"
     xmlns=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:tns=\"{$this->namespace}\"
     xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\"
     xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
     xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:soap-enc=\"http://schemas.xmlsoap.org/soap/encoding/\"></definitions>";

        $dom=new DOMDocument();
        $dom->loadXml($xml);
        $this->addTypes($dom);

        $this->addMessages($dom);
        $this->addPortTypes($dom);
        $this->addBindings($dom);
        $this->addService($dom,$serviceUrl);

        return $dom;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function addTypes($dom)
    {
        if($this->_types===array())
            return;
        $types=$dom->createElement('wsdl:types');
        $schema=$dom->createElement('xsd:schema');
        $schema->setAttribute('targetNamespace',$this->namespace);
        foreach($this->_types as $phpType=>$xmlType)
        {
            if(is_string($xmlType) && strrpos($xmlType,'Array')!==strlen($xmlType)-5)
                continue;  // simple type
            $complexType=$dom->createElement('xsd:complexType');
            if(is_string($xmlType))
            {
                if(($pos=strpos($xmlType,'tns:'))!==false)
                    $complexType->setAttribute('name',substr($xmlType,4));
                else
                    $complexType->setAttribute('name',$xmlType);
                $complexContent=$dom->createElement('xsd:complexContent');
                $restriction=$dom->createElement('xsd:restriction');
                $restriction->setAttribute('base','soap-enc:Array');
                $attribute=$dom->createElement('xsd:attribute');
                $attribute->setAttribute('ref','soap-enc:arrayType');
                $attribute->setAttribute('wsdl:arrayType',substr($xmlType,0,strlen($xmlType)-5).'[]');
                $restriction->appendChild($attribute);
                $complexContent->appendChild($restriction);
                $complexType->appendChild($complexContent);
            }
            else if(is_array($xmlType))
            {
                $complexType->setAttribute('name',$phpType);
                $all=$dom->createElement('xsd:all');
                foreach($xmlType as $name=>$type)
                {
                    $element=$dom->createElement('xsd:element');
                    $element->setAttribute('name',$name);
                    $element->setAttribute('type',$type[0]);
                    $all->appendChild($element);
                }
                $complexType->appendChild($all);
            }
            $schema->appendChild($complexType);
            $types->appendChild($schema);
        }

        $dom->documentElement->appendChild($types);
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function addMessages($dom)
    {
        foreach($this->_messages as $name=>$message)
        {
            $element=$dom->createElement('wsdl:message');
            $element->setAttribute('name',$name);
            foreach($this->_messages[$name] as $partName=>$part)
            {
                if(is_array($part))
                {
                    $partElement=$dom->createElement('wsdl:part');
                    $partElement->setAttribute('name',$partName);
                    $partElement->setAttribute('type',$part[0]);
                    $element->appendChild($partElement);
                }
            }
            $dom->documentElement->appendChild($element);
        }
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function addPortTypes($dom)
    {
        $portType=$dom->createElement('wsdl:portType');
        $portType->setAttribute('name',$this->serviceName.'PortType');
        $dom->documentElement->appendChild($portType);
        foreach($this->_operations as $name=>$doc)
            $portType->appendChild($this->createPortElement($dom,$name,$doc));
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     * @param string $doc doc
     */
    private function createPortElement($dom,$name,$doc)
    {
        $operation=$dom->createElement('wsdl:operation');
        $operation->setAttribute('name',$name);

        $input = $dom->createElement('wsdl:input');
        $input->setAttribute('message', 'tns:'.$name.'Request');
        $output = $dom->createElement('wsdl:output');
        $output->setAttribute('message', 'tns:'.$name.'Response');

        $operation->appendChild($dom->createElement('wsdl:documentation',$doc));
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function addBindings($dom)
    {
        $binding=$dom->createElement('wsdl:binding');
        $binding->setAttribute('name',$this->serviceName.'Binding');
        $binding->setAttribute('type','tns:'.$this->serviceName.'PortType');

        $soapBinding=$dom->createElement('soap:binding');
        $soapBinding->setAttribute('style','rpc');
        $soapBinding->setAttribute('transport','http://schemas.xmlsoap.org/soap/http');
        $binding->appendChild($soapBinding);

        $dom->documentElement->appendChild($binding);

        foreach($this->_operations as $name=>$doc)
            $binding->appendChild($this->createOperationElement($dom,$name));
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     */
    private function createOperationElement($dom,$name)
    {
        $operation=$dom->createElement('wsdl:operation');
        $operation->setAttribute('name', $name);
        $soapOperation = $dom->createElement('soap:operation');
        $soapOperation->setAttribute('soapAction', $this->namespace.'#'.$name);
        $soapOperation->setAttribute('style','rpc');

        $input = $dom->createElement('wsdl:input');
        $output = $dom->createElement('wsdl:output');

        $soapBody = $dom->createElement('soap:body');
        $soapBody->setAttribute('use', 'encoded');
        $soapBody->setAttribute('namespace', $this->namespace);
        $soapBody->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
        $input->appendChild($soapBody);
        $output->appendChild(clone $soapBody);

        $operation->appendChild($soapOperation);
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $serviceUrl Web service URL
     */
    private function addService($dom,$serviceUrl)
    {
        $service=$dom->createElement('wsdl:service');
        $service->setAttribute('name', $this->serviceName.'Service');

        $port=$dom->createElement('wsdl:port');
        $port->setAttribute('name', $this->serviceName.'Port');
        $port->setAttribute('binding', 'tns:'.$this->serviceName.'Binding');

        $soapAddress=$dom->createElement('soap:address');
        $soapAddress->setAttribute('location',$serviceUrl);
        $port->appendChild($soapAddress);
        $service->appendChild($port);
        $dom->documentElement->appendChild($service);
    }

    /**
     * 显示wsdl文件
     *
     * @param string $providerClass
     * @param string $serviceUrl
     */
    public function renderWsdl() {

        $wsdlContent = $this->createWsdl($this->providerClass, $this->serviceUrl, $this->encoding);

        header('Content-Type: text/xml;charset='.$this->encoding);
        header('Content-Length: '.(function_exists('mb_strlen') ? mb_strlen($wsdlContent,'8bit') : strlen($wsdlContent)));

        echo $wsdlContent;
    }
}