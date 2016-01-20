<?php

class CertModel extends Model
{
    public function tableName(){
        return 'c_certification';
    }

    public function createCert($params = array()) {
        return $this->insert($params);
    }

    public function updateCert($params = array(),$where = array()){
        if(!$params || !$where){
            Log::error('updateCert update all or SET is empty');
            return false;
        }
        
        return $this->update($params,$where);
    }
    
    public function deleteCert($params = array()){
        if(!$params){
            Log::error('deleteCert delete all');
            return false;
        }
        
        return $this->delete($params);
    }

    public function getList($params = array() , $fields = '*') {
        return $this->where($params)->from(null,$fields)->select();
    }
}