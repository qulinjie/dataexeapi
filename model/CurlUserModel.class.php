<?php

class CurlUserModel extends CurlModel
{
    public function erp_login($params = array()){
        $interface = $params['url'];
        unset($params['url']);
        return self::sendRequestErp($interface, $params);
    }
    
}