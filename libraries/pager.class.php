<?php
/**
 * pager class file
 *
 * 分页类
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: pager.class.php 1.3 2011-11-13 21:10:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class pager extends Base {

    /**
     * pager的css文件.
     *
     * @var string
     */
    private $style;

    /**
     * 连接网址
     *
     * @var string
     */
    public $url;

    /**
     * 当前页
     *
     * @var integer
     */
    public $page;

    /**
     * list总数
     *
     * @var integer
     */
    public $total;

    /**
     * 分页总数
     *
     * @var integer
     */
    public $totalPages;

    /**
     * 每个页面显示的post数目
     *
     * @var integer
     */
    public $num;

    /**
     * list允许放页码数量,如:1.2.3.4就这4个数字,则$perCircle为4
     *
     * @var integer
     */
    public $perCircle;

    /**
     * 分页程序的扩展功能开关,默认关闭
     *
     * @var boolean
     */
    public $ext;

    /**
     * list中的坐标. 如:7,8,九,10,11这里的九为当前页,在list中排第三位,则$center为3
     *
     * @var integer
     */
    public $center;

    /**
     * 第一页
     *
     * @var string
     */
    public $firstPage;

    /**
     * 上一页
     *
     * @var string
     */
    public $prePage;

    /**
     * 下一页
     *
     * @var string
     */
    public $nextPage;

    /**
     * 最后一页
     *
     * @var string
     */
    public $lastPage;

    /**
     * 分页附属说明
     *
     * @var string
     */
    public $note;

    /**
     * 是否为ajax分页模式
     *
     * @var boolean
     */
    public $isAjax;

    /**
     * ajax分页的动作名称
     *
     * @var string
     */
    public $ajaxActionName;

    /**
     * 分页css名
     *
     * @var string
     */
    public $styleFile;

    /**
     * 分页隐藏开关
     *
     * @var boolean
     */
    public $hiddenStatus;

	
    /**
     * URL跟参数
     * @var string
     */
	public  $queryString;

    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->ext                = false;
        $this->center             = 3;
        $this->num                = 10;
        $this->perCircle          = 10;
        $this->isAjax             = false;
        $this->hiddenStatus       = false;

        //define pager style params   
        $this->firstPage         = '首页';  // change by Mark 2015/8/20
        $this->prePage           = '<<';
        $this->nextPage          = '>>';
        $this->lastPage          = '尾页';

        return true;
    }

    /**
     * 获取总页数
     *
     * @return integer
     */
    private function getTotalPage() {

        if (!$this->total) {
            return false;
        }

        return ceil($this->total / $this->num);
    }

    /**
     * 获取当前页数
     *
     * @return integer
     */
    private function getPageNum() {

        $page = (!$this->page) ? 1 : (int)$this->page;

        //当URL中?page=5的page参数大于总页数时
        return ($page > $this->totalPages) ? (int)$this->totalPages : $page;
    }

    /**
     * 返回$this->num=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function num($num = null) {

        //参数分析
        if (is_null($num)) {
            $num = 10;
        }

        $this->num = (int)$num;

        return $this;
    }

    /**
     * 返回$this->total=$totalPost.
     *
     * @param integer $totalPost
     * @return $this
     */
	public function total($totalPost = null)
	{
        $this->total = ($totalPost>0) ? (int)$totalPost : 0;
        return $this;
    }

    /**
     * 开启分页的隐藏功能
     *
     * @access public
     * @param boolean $item    隐藏开关 , 默认为true.
     * @return $this
     */
	public function hide($item = true) 
	{
        if ($item === true) {
            $this->hiddenStatus = true;
        }
        return $this;
    }

    /**
     * 返回$this->url=$url.
     *
     * @param string $url
     * @return $this
     */
    public function url($url = null) {

        //当url为空时,自动获取url参数. 注:默认当前页的参数为page
        if (is_null($url)) {

            //当网址没有参数时
            $url = (!$_SERVER['QUERY_STRING']) ? $_SERVER['REQUEST_URI'] . ((substr($_SERVER['REQUEST_URI'], -1) == '?') ? 'page=' : '?page=') : '';

            //当网址有参数时,且有分页参数(page)时
            if (!$url && (stristr($_SERVER['QUERY_STRING'], 'page='))) {
                $url = str_ireplace('page=' . $this->page, '', $_SERVER['REQUEST_URI']);

                $end_str = substr($url, -1);
                if ($end_str == '?' || $end_str == '&') {
                    $url .= 'page=';
                } else {
                    $url .= '&page=';
                }
            }

            //当网址中未发现含有分页参数(page)时
            if (!$url) {
                $url = $_SERVER['REQUEST_URI'] . '&page=';
            }
        }

        //自动获取都没获取到url...额..没有办法啦, 趁早返回false
        if (!$url) {
            Controller::halt('The argument of method : url() invalid in pager class!');
        }

        $this->url = trim($url);

        return $this;
    }

    /**
     * 返回$this->page=$page.
     *
     * @param integer $page
     * @return $this
     */
    public function page($page = null) {

        //当参数为空时.自动获取GET['page']
        if (is_null($page)) {
            $page = (int)Controller::get('page');
            $page = (!$page) ? 1 : $page;
        }

		( $page > $this->total ) ? ( $page=$this->total ) : false;
		( $page < 1) ? ( $page=1 ) : false;

        $this->page = $page;

        return $this;
    }

    /**
     * 返回$this->ext=$ext.
     *
     * @param boolean $ext
     * @return $this
     */
    public function ext($ext = true) {

        //将$ext转化为小写字母.
        $this->ext = ($ext) ? true : false;

        return $this;
    }

    /**
     * 返回$this->center=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function center($num) {

        if (!$num) {
            return false;
        }

        $this->center = (int)$num;

        return $this;
    }

    /**
     * 返回$this->perCircle=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function circle($num) {

        if (!$num) {
            return false;
        }

        $this->perCircle = (int)$num;

        return $this;
    }

    /**
     * 处理第一页,上一页
     *
     * @return string
     */
    private function getFirstPage() {

        if ($this->page == 1 || $this->totalPages <= 1) {
            return false;
        }

        if ($this->isAjax === true) {
            $string = '<li class="pagelist_ext"><a href="' . $this->url . '1" onclick="' . $this->ajaxActionName . '(\'' . $this->url . '1\'); return false;">' . $this->firstPage . '</a></li><li class="pagelist_ext"><a href="' . $this->url . ($this->page - 1). '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . ($this->page - 1). '\'); return false;">' . $this->prePage . '</a></li>';
        } else {
			$string = '
				<li><a href="' . $this->url . '1" target="_self">' . $this->firstPage . '</a></li> 
				<li><a aria-label="Previous" href="' . $this->url . ($this->page - 1). '" target="_self">' . $this->prePage . '</a></li>
				';
        }

        return $string;
    }

    /**
     * 处理下一页,最后一页
     *
     * @return string
     */
    private function getLastPage() {

        if ($this->page == $this->totalPages || $this->totalPages <= 1) {
            return false;
        }

        if ($this->isAjax === true) {
            $string = '<li class="pagelist_ext"><a href="' . $this->url . ($this->page + 1) . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . ($this->page + 1) . '\'); return false;">' . $this->nextPage . '</a></li><li class="pagelist_ext"><a href="' . $this->url . $this->totalPages . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . $this->totalPages . '\'); return false;">' . $this->lastPage . '</a></li>';
        } else {
			$string = '<li><a aria-label="Next" href="' . $this->url . ($this->page + 1) . '" target="_self">' . $this->nextPage . '</a></li>
					   <li><a href="' . $this->url . $this->totalPages . '" target="_self">' . $this->lastPage . '</a></li>';
        }
        return $string;
    }

    /**
     * 处理注释内容
     *
     * @return string
     */
    private function getNote() {

        if (!$this->ext || !$this->note) {
            return false;
        }

        return str_replace(array('{$totalNum}', '{$total_page}', '{$num}'), array($this->total, $this->totalPages, $this->num), $this->note);
    }

    /**
     * 处理list内容
     *
     * @return string
     */
    private function getList() {

        if (empty($this->totalPages) || empty($this->page)) {
            return false;
        }

        if ($this->totalPages > $this->perCircle) {
            if ($this->page + $this->perCircle >= $this->totalPages + $this->center) {
                $list_start   = $this->totalPages - $this->perCircle + 1;
                $list_end     = $this->totalPages;
            } else {
                $list_start   = ($this->page>$this->center) ? $this->page - $this->center + 1 : 1;
                $list_end     = ($this->page>$this->center) ? $this->page + $this->perCircle-$this->center : $this->perCircle;
            }
        } else {
            $list_start       = 1;
            $list_end         = $this->totalPages;
        }

        $pageListQueue = '';
        for ($i=$list_start; $i<=$list_end; $i++) {
			$pageListQueue  .= ($this->page == $i) 
				? '<li  class="active" ><a href="javascript:;">' . $i . '</a></li>' 
				: (
					($this->isAjax === true) 
					? '<li><a href="' . $this->url . $i . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . $i . '\'); return false;">' . $i . '</a></li>' 
					: '<li><a href="' . $this->url . $i . '" target="_self">' . $i . '</a></li>'
				);
        }

        return $pageListQueue;
    }

    /**
     * 开启ajax分页模式
     *
     * @param string $action    动作名称
     * @return $this
     */
    public function ajax($action) {

        if ($action) {
            $this->isAjax           = true;
            $this->ajaxActionName   = $action;
        }

        return  $this;
    }

    /**
     * 输出处理完毕的HTML
     *
     * @return string
     */
    public function output() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

		$queryString = $_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '';
		$queryString = preg_replace('/&?page=[0-9]*/', '', $queryString);
		$this->queryString = $queryString;

        //获取总页数.
        $this->totalPages  = $this->getTotalPage();

        //获取当前页.
        $this->page        = $this->getPageNum();

		return (($this->hiddenStatus === true) && ($this->total <= $this->num)) ? '' 
			: '<nav>
				<ul class="pagination">' . $this->getNote() . $this->getFirstPage() . $this->getList() . $this->getLastPage() . '</ul>
			</nav>';

		return <<<"EOT"
						<nav>
						  <ul class="pagination">
							<li>
							  <a href="#" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span>
							  </a>
							</li>
							<li><a href="#">1</a></li>
							<li><a href="#">2</a></li>
							<li><a href="#">3</a></li>
							<li><a href="#">4</a></li>
							<li><a href="#">5</a></li>
							<li>
							  <a href="#" aria-label="Next">
								<span aria-hidden="true">&raquo;</span>
							  </a>
							</li>
						  </ul>
						</nav>
EOT;
    }

    /**
     * 输出下拉菜单式分页的HTML(仅限下拉菜单)
     *
     * @return string
     */
    public function select() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

        //获取总页数.
        $this->totalPages  = $this->getTotalPage();

        //获取当前页.
        $this->page        = $this->getPageNum();

        $string = '<select name="doitphp_select_pagelist" class="pagelist_select_box" onchange="self.location.href=this.options[this.selectedIndex].value">';
        for ($i = 1; $i <= $this->totalPages; $i ++) {
            $string .= ($i == $this->page) ? '<option value="' . $this->url . $i . '" selected="selected">' . $i . '</option>' : '<option value="' . $this->url . $i . '">' . $i . '</option>';
        }
        $string .= '</select>';

        return $string;
    }

    /**
     * 加载pager的CSS文件
     *
     * @param string $styleName
     * @return string
     */
    public function loadCss($styleName = 'classic') {

        //设置分页CSS样式
        if (!$this->styleFile) {
            $this->setMode($styleName);
        }

        $cssFile = Controller::getBaseUrl() . 'assets/doit/images/' . $this->styleFile;

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $cssFile . "\"/>\r";
    }

    /**
     * 视图中加载pager的CSS文件
     *
     * @param string $styleName
     * @return string
     */
    public static function loadCssFile($styleName = 'classic') {

        //设置分页CSS样式
        switch ($styleName) {
            case 'classic':
                $styleFile         = 'doitphp_pagelist_classic.min.css';
                break;

            case 'simple':
                $styleFile         = 'doitphp_pagelist_simple.min.css';
                break;

            default:
                $styleFile         = 'doitphp_pagelist_default.min.css';
        }

        $cssFile = Controller::getBaseUrl() . 'assets/doit/images/' . $styleFile;

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $cssFile . "\"/>\r";
    }

    /**
     * 设置分页的样式
     *
     * @param string $styleName    分页样式名
     * @return $this
     */
    public function setMode($styleName = 'classic') {

        switch ($styleName) {

            case 'classic':
                $this->styleFile         = 'doitphp_pagelist_classic.min.css';
                break;

            case 'simple':
                $this->styleFile         = 'doitphp_pagelist_simple.min.css';

                $css_dir = Controller::getBaseUrl() . 'assets/doit/';
                $this->firstPage         = '<img src="'.$css_dir.'images/pre_02.gif" width="17" height="11" />';
                $this->prePage           = '<img src="'.$css_dir.'images/s_pre.gif" width="16" height="11" />';
                $this->nextPage          = '<img src="'.$css_dir.'images/s_next.gif" width="14" height="11" />';
                $this->lastPage          = '<img src="'.$css_dir.'images/next_02.gif" width="15" height="11" />';
                break;

            default:
                $this->styleFile         = 'doitphp_pagelist_default.min.css';
                $this->note              = '<li class="pagelist_note">共{$totalNum}条{$total_page}页 {$num}条/页</li>';
        }

        return $this;
    }
}
