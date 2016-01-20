<?php

class CertController extends  BaseController
{
    public function handle($params = [],$req_data=[])
    {
        switch($params[0]) {
            case 'create':
                $this->create($req_data);
                break;
            case 'update':
                $this->update($req_data);
                break;
            case 'delete':
                $this->delete($req_data);
                break;
            case 'getList':
                $this->getList($req_data);
                break;
            default:
                Log::error('Cert method not exist params='.$params[0]);
                EC::fail(EC_MTD_NON);
        }
    }
    
    private function create($req_data){
        $req_data['id'] = $this->model('id')->getCertificationId();
        if(!$this->model('cert')->createCert($req_data)){
            Log::error('Cert create error params<<<'.var_export($req_data,true));
            EC::fail(EC_UPD_REC);
        }
        
        EC::success(EC_OK,array('cert_id' => $req_data['id']));
    }
    
    private function update($req_data){
        $where = array('id' => $req_data['id']);
        unset($req_data['id']);
        
        if(!$this->model('cert')->updateCert($req_data,$where)){
            Log::error('Cert update error params<<<'.var_export($req_data,true));
            Log::error('Cert update error where <<<'.var_export($where,true));
            EC::fail(EC_UPD_REC);
        }
        
        EC::success(EC_OK);
    }
    
    private function delete($req_data){
        if(!$this->model('cert')->deleteCert($req_data)){
            Log::error('Cert delete error params<<<'.var_export($req_data,true));
            EC::fail(EC_UPD_REC);
        }
        
        EC::success(EC_OK);        
    }

    private function getList($req_data){
        $fields = '*';
        if(isset($req_data['fields'])){
            $fields = $req_data['fields'];
            unset($req_data['fields']);
        }
        
        $data = $this->model('cert')->getList($req_data,$fields); 
	    EC::success(EC_OK,$data);
	}
}