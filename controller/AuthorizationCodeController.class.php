<?php
/**
 * 授权码 
 * @author zhangkui
 *
 */
class AuthorizationCodeController extends BaseController {

     public function handle($params = array(), $req_data = array()) {
        if (empty($params)) {
            Log::error('AuthorizationCodeController . params is empty . ');
            EC::fail(EC_MTD_NON);
        } else {
            switch ($params[0]) {
                case 'searchCnt':
                    $this->getSearchCnt($req_data);
                    break;
                case 'searchList':
                    $this->getSearchList($req_data);
                    break;
                default:
                    Log::error('page not found . ' . $params[0]);
                    EC::fail(EC_MTD_NON);
                    break;
            }
        }
    }
    
    public function getSearchCnt($req_data){
        $code_model = $this->model('authorizationCode');
        $data = $code_model->getSearchCnt($req_data);
        EC::success(EC_OK,$data);
    }
    
    public function getSearchList($req_data){
        $current_page = $req_data['current_page'];
        $page_count = $req_data['page_count'];
        unset($req_data['current_page']);
        unset($req_data['page_count']);
        $params = $req_data;
    
        $code_model = $this->model('authorizationCode');
        $data = $code_model->getSearchList($params, $current_page, $page_count);
    
        EC::success(EC_OK,$data);
    }
    
    /* 
    protected function searchList()
    {
        // 获取查询条件
        $condition = $this->getConditionArr();
    
        // 总条数
        $codeModel = $this->model( 'authorizationCode' );
        $total = $codeModel->getSearchCnt( $condition['condition'] );
        
        // 分页
        $pager_html = $this->getPageHtml( $condition['condition']['page'], $total, $condition['condition']['count'] );
    
        // 获取数据
        $objects = $codeModel->getSearchList( $condition['condition'] );
    
        $data = [];
        $data['queryString'] = $this->getQueryString();
        $data['params'] = $condition['params'];
        $data['objects'] = $objects;
        $data['page'] = $condition['condition']['page'];
        $data['numPerPage'] = $condition['condition']['count'];
        $data['pager_html'] = $pager_html;
    
        Log::notice('success  ==== >>> data=' . json_encode($data));
        EC::success(EC_OK, $data); 
    }
    
    private function getConditionArr()
    {
        // 获取条件
        $conditionArr = $params = [];
    
        //TODO for test
        $conditionArr['user_id'] = 1000;//$this-> getLoginUserId();
        
        $shipping_type = (int)$this->get('type', 0);
        if ( $shipping_type ) {
            $conditionArr['type'] = $shipping_type;
            $params['type'] = $shipping_type;
        }
    
        $status = (int)$this->get('status', 0 );
        if ( $status ) {
            $conditionArr['status'] = $status;
            $params['status'] = $status;
        }
    
        $start_date  = $this->get('start_date', 0);
        if ( $start_date ) {
            $conditionArr['start_date'] = $start_date;
            $params['start_date'] = $start_date;
        }
    
        $end_date = $this->get('end_date', 0 );
        if ( $end_date ) {
            $conditionArr['end_date'] = $end_date;
            $params['end_date'] = $end_date;
        }
    
        $count = (int)$this->get('count', 10 );
        if ( $count ) {
            $conditionArr['count'] = $count;
            $params['count'] = $count;
        }
    
        $page = (int)$this->get('page', 1 );
        if ( $page ) {
            $conditionArr['page'] = $page;
            $params['page'] = $page;
        }
    
        Log::notice('getConditionArr ==== >>> condition=' . json_encode($conditionArr) . ',params=' . json_encode($params) );
        return ['condition'=>$conditionArr, 'params'=>$params];
    } */
    
}