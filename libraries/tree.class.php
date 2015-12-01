<?php
/**
 * tree class file
 *
 * 无限分类
 * @author DaBing<InitPHP>, tommy
 * @copyright  CopyRight DoitPHP team, initphp team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: tree.class.php 1.3 2011-11-20 14:01:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class tree extends Base {

    /**
     * 分类的父ID的键名(key)
     *
     * @var integer
     */
    private $parentId;

    /**
     * 分类的ID(key)
     *
     * @var integer
     */
    private $id;

    /**
     * 分类名
     *
     * @var string
     */
    private $name;

    /**
     * 子分类名
     *
     * @var string
     */
    private $child;


    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

        $this->parentId  = 'parentId';
        $this->id        = 'id';
        $this->name      = 'name';
        $this->child     = 'child';

        return true;
    }

    /**
     * 无限级分类树-初始化配置
     *
     * @param  array $config 配置分类的键
     * @return $this
     *
     * @example
     *
     * 法一：
     * $params = array('parentId'=>'pid', 'id' => 'cat_id', 'name' =>'cat_name');
     * $this->config($params );
     *
     * 法二：
     * $params = array('parentId'=>'pid', 'id' => 'cat_id', 'name' =>'cat_name', 'child'=>'node');
     * $this->config($params );
     */
    public function config($params) {

        //parse params
        if (!$params || !is_array($params)) {
            return false;
        }

        $this->parentId     = (isset($params['parentId'])) ? $params['parentId'] : $this->parentId;
        $this->id           = (isset($params['id'])) ? $params['id'] : $this->id;
        $this->name         = (isset($params['name'])) ? $params['name'] : $this->name;
        $this->child        = (isset($params['child'])) ? $params['child'] : $this->child;

        return $this;
    }

    /**
     * 无限级分类树-获取树
     *
     * 用于下拉框select标签的option内容
     * @param  array     $data              树的数组
     * @param  int       $parentId         初始化树时候，代表ID下的所有子集
     * @param  int       $selectId      	选中的ID值
     * @param  string      $prefix          前缀
     * @return string|array
     */
    public function getHtmlOption($data, $parentId = 0, $selectId = null, $preFix = '|-') {

        //parse params
        if (!$data || !is_array($data)) {
            return '';
        }

        $string = '';
        foreach ($data as $key => $value) {
            if ($value[$this->parentId] == $parentId) {
                $string .= '<option value=\'' . $value[$this->id] . '\'';
                if (!is_null($selectId)) {
                    $string .= ($value[$this->id] == $selectId) ? ' selected="selected"' : '';
                }
                $string .= '>' . $preFix . $value[$this->name] . '</option>';
                $string .= $this->getHtmlOption($data, $value[$this->id], $selectId, '&nbsp;&nbsp;' . $preFix);
            }
        }

        return $string ;
    }

    /**
     * 获取无限分类树
     *
     * @access public
     * @param array $data 数组
     * @param integer $parentId 父ID
     * @return array
     */
    public function getTree($data, $parentId = 0) {

        //parse params
        if (!$data || !is_array($data)) {
            return '';
        }

        //get child tree array
        $childArray = $this->getChild($data, $parentId);
        //当子分类无元素时,结果递归
        if(!sizeof($childArray)) {
            return '';
        }

        $treeArray = array();
        foreach ($childArray as $lines) {
            $treeArray[] = array(
            $this->id    => $lines[$this->id],
            $this->name  => $lines[$this->name],
            $this->child => $this->getTree($data, $lines[$this->id]),
            );
        }

        return $treeArray;
    }

    /**
     * 无限级分类树-获取子类
     *
     * @param  array $data 树的数组
     * @param  int   $id   父类ID
     * @return string|array
     */
    public function getChild($data, $id) {

        //parse params
        if (!$data || !is_array($data)) {
            return array();
        }

        $tempArray = array();
        foreach ($data as $value) {
            if ($value[$this->parentId] == $id) {
                $tempArray[] = $value;
            }
        }

        return $tempArray;
    }

    /**
     * 无限级分类树-获取父类
     *
     * @param  array $data 树的数组
     * @param  int   $id   子类ID
     * @return string|array
     */
    public function getParent($data, $id) {

        //parse params
        if (!$data || !is_array($data)) {
            return array();
        }

        $temp = array();
        foreach ($data as $vaule) {
            $temp[$vaule[$this->id]] = $vaule;
        }

        $parentId = $temp[$id][$this->parentId];

        return $temp[$parentId];
    }

}