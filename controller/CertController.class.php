<?php

class CertController extends  BaseController
{
    public function handle($params = [],$req_data=[])
    {
        switch($params[0]) {
            case 'getInfo':
                $this->getInfo($req_data);
                break;
        }
    }

    private function getInfo($req_data){
	    if(!$data = $this->model('certification')->get(['user_id' => $req_data['user_id']])){
	        Log::error('Cert getInfo error params<<<<<<<<<<'.var_export($req_data,true));
	        EC::success(EC_OK);
	    }
	        
	    EC::success(EC_OK,$data[0]);
	}
}