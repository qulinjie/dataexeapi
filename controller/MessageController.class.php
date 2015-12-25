<?php

class MessageController extends BaseController
{
    public function handle($params = array(), $req_data = array())
    {
        switch($params[0]){
            case 'getCnt':
                $this->getCnt($req_data);
                break;
            case 'searchList':
                $this->searchList($req_data);
                break;
            default:
                Log::error('Message params is error');
                EC::fail(EC_MTD_NON);
        }
    }

    private function getCnt($req_data)
    {
        $session = self::instance('session');
        if(!$loginUser = $session->get('loginUser')){
            Log::error('getCnt user not login');
            EC::fail(EC_NOT_LOGIN);
        }

        EC::success(EC_OK,$this->model('message')->getCnt($loginUser['id']));
    }

    private function searchList($req_data)
    {
        $session = self::instance('session');
        if(!$loginUser = $session->get('loginUser')){
            Log::error('getCnt user not login');
            EC::fail(EC_NOT_LOGIN);
        }

        EC::success(EC_OK,$this->model('message')->searchList($loginUser['id'],$req_data['current_page'],$req_data['page_count']));
    }

    /**
     * @param $user_id
     * @param $number
     * @return bool
     */
    public static function addMsg($user_id,$number)
    {
        $id = self::model('id')->getMessageId();
        if(!self::model('message')->add($id,$user_id,$number)){
            Log::error('MessageController addMsg fail msg('.self::model('message')->getErrorInfo().')');
            return false;
        }
        return true;
    }
}