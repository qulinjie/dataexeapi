<?php

/**
 * Created by PhpStorm.
 * User: zhangsong
 * Date: 2016/1/13
 * Time: 10:41
 */
class CertController extends  BaseController
{
    public function handle($params = [],$req_data=[])
    {
        switch($params[0]) {
            case 'get':
                $this->getInfo($req_data);
                break;
        }
    }

    private function getInfo($req_data){
        $session = $this->instance('session');
        if(!$loginUser = $session->get('loginUser')){
            Log::error('CertController getInfo not login') ;
            EC::fail(EC_NOT_LOGIN);
        }

        EC::success(EC_OK,$this->model('certification')->get($loginUser['id']));

    }
}