<?php
/**
 * @author zhangkui
 *
 */
class BcsChildAccountController extends BaseController {

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
                case 'delete':
                    $this->delete($req_data);
                    break;
                default:
                    Log::error('method not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('bcsChildAccount');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('bcsChildAccount');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    public function update($req_data){
        $id = $req_data['bcs_trade_id'];

        $params = array();
        $params['FMS_TRANS_NO'] = $req_data['FMS_TRANS_NO']; // 资金监管系统交易流水号
        $params['TRANS_TIME'] = $req_data['TRANS_TIME']; // 交易完成时间 时间格式：YYYY-MM-DD HH24:MI:SS
        $params['comment'] = $req_data['comment'];
        $params['status'] = $req_data['status'];
        
        $bcsChildAccount_model = $this->model('bcsChildAccount');
        $res = $bcsChildAccount_model->updateBcsChildAccount($params,array('id' => $id));
        if(false === $res){
            Log::error('updateBcsChildAccount faild !');
            EC::fail(EC_UPD_REC);
        }
        EC::success(EC_OK);
    }
    
    public function getInfo($req_data){
        $code_model = $this->model('bcsChildAccount');
        $data = $code_model->getInfoBcsChildAccount($req_data, array());
        EC::success(EC_OK,$data);
    }
    
    public function create($req_data){
        $id = $this->model('id')->getBcsChildAccountId();
        $req_data['id'] = $id;
        $bcsChildAccount_model = $this->model('bcsChildAccount');
        $data = $bcsChildAccount_model->createBcsChildAccount($req_data);
        if(false === $data){
            Log::error('createBcsChildAccount Fail! rollback .');
            EC::fail(EC_ADD_REC);
        }
        EC::success(EC_OK,$id);
    }
    
    public function delete($req_data){
        $bcsChildAccount_model = $this->model('bcsChildAccount');
        $res = $bcsChildAccount_model->deleteChildAccount($req_data);
        if(false === $res){
            Log::error('delete faild !');
            EC::fail(EC_DEL_FAI);
        }
        EC::success(EC_OK);
    }
    
}