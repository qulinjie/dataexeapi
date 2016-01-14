<?php
class AdminModel extends Model {
    public function tableName(){
        return 'c_admin';
    }
    
    public function getAdminInfo($params = array(), $page = null, $count = null){
        Log::notice('getAdminInfo ==>> params=' . var_export($params, true));
        $model = $this->from();
        $data = null;
        if($page === null && $count === null) {
            $data = $model->where($params)->order('add_timestamp desc')->select();
        }else {
            $data = $model->where($params)->order('add_timestamp desc')->pageLimit($page, $count)->select();
        }
        if(!$data){
            Log::error('AdminInfo not find ');
            return array();
        }
        return $data[0];
    }
    
    public function updateAdmin($param, $where){
        if(empty($where)){
            Log::error('!!! upate all rows of user');
            return false;
        }
        Log::notice('upupdateAdmindate ==>> param=' . var_export($param, true));
        if(empty($param)){
            return false;
        }
        return $this->update($param, $where);
    }
    
}