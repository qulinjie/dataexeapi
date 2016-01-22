<?php
/**
 * @author zhangkui
 *
 */
class BcsRegisterController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('Controller . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
                case 'searchCnt':
                    $this->getSearchCnt($req_data);
                    break;
                case 'searchList':
                    $this->getSearchList($req_data);
                    break;
                case 'update':
                    $this->update($req_data);
                    break;
                case 'getList':
                    $this->getList($req_data);
                    break;
                case 'getInfo':
                    $this->getInfo($req_data);
                    break;
                case 'create':
                    $this->create($req_data);
                    break;             
                default:
                    Log::error('method not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $where = array('id' => $req_data['id']);
        unset($req_data['id']);
      
        if(!$this->model('bcsRegister')->updateBcsRegister($req_data,$where)){
            Log::error('updateBcsRegister fail !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getList($req_data){
        $fields  = '*';
        if(isset($req_data['fields'])){
            $fields = $req_data['fields'];
            unset($req_data['fields']);
        }
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getList($req_data, $fields);
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){      
        $req_data['id']     = $this->model('id')->getBcsRegisterId();
        $req_data['SIT_NO'] = $this->model('id')->getSitNo();
        
        if(!$this->model('bcsRegister')->createBcsRegister($req_data)){
            Log::error('bcsRegister create Fail!');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,['SIT_NO' => $req_data['SIT_NO'],'id' => $req_data['id']]);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsRegister');
        $data = $code_model->getInfoBcsRegister($req_data, array());
        EC::success(EC_OK,$data);
    }
    
}