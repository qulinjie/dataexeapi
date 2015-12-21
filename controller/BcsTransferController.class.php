<?php
/**
 * @author zhangkui
 *
 */
class BcsTransferController extends BaseController {

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
        $code_model = $this->model('bcsTransfer');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsTransfer');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['id'];
        unset($req_data['id']);
        
        $bcsTransfer_model = $this->model('bcsTransfer');
        $res = $bcsTransfer_model->updateBcsTransfer($req_data,array('id' => $id));
        if(false === $res){
            Log::error('updateBcsTransfer faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsTransfer');
        $data = $code_model->getInfoBcsTransfer($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getBcsTransferId();
        $req_data['id'] = $id;
        
        $bcsTransfer_model = $this->model('bcsTransfer');
        $data = $bcsTransfer_model->createBcsTransfer($req_data);
        if(false === $data){
            Log::error('createBcsTransfer Fail! rollback .');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,$id);
    }
    
}