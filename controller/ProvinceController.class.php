<?php

class ProvinceController extends Controller {

    public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) { 
                case 'getInfo':
                    $this->getInfo($req_data);
                    break;
                case 'getList':
                    $this->getList($req_data);
                    break;
                default:
                    Log::error('page not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }  
            
    public function getInfo($req_data) {
        $province_model = $this->model('province'); 
        $data = $province_model->getInfo($req_data);        
        EC::success(EC_OK, $data);
    }   
    
    public function getList($req_data) {
    	$province_model = $this->model('province');
    	$data = $province_model->getList($req_data);
    	EC::success(EC_OK, $data);
    }
    
}