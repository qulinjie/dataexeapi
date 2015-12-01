<?php
/**
 * Model.class.php
 *
 * DoitPHP 系统model的基类
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Model.class.php 1.3 2011-12-18 20:30:00Z tommy $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Model extends Base {

    /**
     * 数据表名
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * 数据表字段信息
     *
     * @var array
     */
    protected $tableField = array();

    /**
     * 数据表的主键信息
     *
     * @var string
     */
    protected $primaryKey = null;

    /**
     * model所对应的数据表名的前缀
     *
     * @var string
     */
    protected $prefix = null;

    /**
     * 数据库连接的实例化对象
     *
     * @var $object
     */
    protected $db = null;

    /**
     * 数据表信息缓存文件存放目录
     *
     * @var string
     */
    protected $cacheDir = null;

    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $_parts = array();

    /**
     * 查询数据临时存放容器
     *
     * @var object
     */
    protected $myrow = null;

    /**
     * 构造函数
     *
     * 用于初始化程序运行环境，或对基本变量进行赋值
     * @access public
     * @return boolean
     */
    public function __construct() {

        //加载数据库配置文件.
        $params = $this->getConfig();

        //分析,检测配置文件内容
        if (!is_array($params)) {
            Log::error('Contents of the file: config is not correct! It must be an array.');
            EC::fail_page(EC_CNF_ERR);
        }

        //对参数进行trim()数据处理
        foreach ($params as $key=>$value) {
            $params[$key] = trim($value);
        }

        //分析默认参数，默认编码为:utf-8
        $params['charset']     = (isset($params['charset']) && $params['charset']) ? $params['charset'] : 'utf8';
        //获取数据表前缀，默认为空
        $this->prefix          = (isset($params['prefix']) && $params['prefix']) ? $params['prefix'] : '';

        //定义model缓存文件目录
        $this->cacheDir        = CACHE_DIR . 'models' . DIRECTORY_SEPARATOR;

        //用工厂模式实例化数据库驱动类
        $this->db              = $this->factory($params);

        if($this->db == false){
        	EC::fail(EC_DB_CNT);
        }
        //将数据库的用户名及密码及时从内存中注销，提高程序安全性
        unset($params['username'], $params['password']);

        return true;
    }


    /**
     * 第一部分：获取数据表的数据表名，主键，字段信息，等有关信息
     */


    /**
     * 获取当前model所对应的数据表的名称
     *
     * 注:若数据表有前缀($prefix)时，将自动加上数据表前缀。
     * @access protected
     * @return string    数据表名
     */
    protected function getTableName() {

        //当$this->tableName不存在时
        if (!$this->tableName) {
            //获取当前model的类名
            $tableName = $this->tableName();
            $modelId   = ($tableName == true) ? trim($tableName) : substr(strtolower(get_class($this)), 0, -5);
            //分析数据表名，当有前缀时，加上前缀
            $this->tableName = !empty($this->prefix) ? $this->prefix . $modelId : $modelId;
        }

        return $this->tableName;
    }

    /**
     * 获取数据表主键
     *
     * @access protected
     * @return string    数据表主键
     */
    protected function getPrimaryKey() {

        //当$this->primaryKey内容为空时
        if (!$this->primaryKey) {
            $primaryKey = $this->primaryKey();
            //当model中没有定义主键时,则从缓存文件中获取
            if (!$primaryKey) {
                //加载缓存文件不存在时,则创建缓存文件
                if (!$this->loadCache()) {
                    $this->createCache();
                }
            } else {
                $this->primaryKey = trim($primaryKey);
            }
        }

        return $this->primaryKey;
    }

    /**
     * 定义数据表名
     *
     * 在继承类中重载本方法可以定义所对应的数据表的名称
     * @access protected
     * @return string
     */
    protected function tableName() {

        return '';
    }

    /**
     * 定义数据表主键
     *
     * 在继承类中重载本方法可以定义所对应的数据表的主键
     * @access protected
     * @return string
     */
    protected function primaryKey() {

        return '';
    }

    /**
     * 获取数据表字段信息
     *
     * 主键及所有字段信息,返回数据类型为数组
     * @access protected
     * @return array    字段信息
     */
    protected function getTableInfo() {

        //获取数据表名
        $this->getTableName();

        //查询数据表字段信息
        $sql="SHOW FIELDS FROM {$this->tableName}";

        return $this->db->getArray($sql);
    }

    /**
     * 获取数据表字段信息
     *
     * @access protected
     * @return array    数据表字段信息
     */
    protected function getTableFields() {

        //当$this->tableField内容为空时,则加载model缓存文件
        if (!$this->tableField) {
            $tableFields = $this->tableFields();
            //当model文件没有定义数据表字段信息时
            if (!$tableFields) {
                //加载model缓存文件失败或缓存文件不存在时,创建model缓存文件
                if (!$this->loadCache()) {
                    $this->createCache();
                }
            } else {
                $this->tableField = $tableFields;
            }
        }

        return $this->tableField;
    }

    /**
     * 定义数据表字段信息
     *
     * 在继承类中重载本方法可以定义所对应的数据表的字段信息
     * @access protected
     * @return array
     */
    protected function tableFields() {

        return array();
    }

    /**
     * 获取数据表前缀
     *
     * @access public
     * @return string
     */
    public function getTablePrefix() {

        return $this->prefix;
    }

    /**
     * 创建当前model的缓存文件
     *
     * 用于创建当前model的缓存文件，用于减轻数据反复查询数据表字段信息的操作，从而提高程序的运行效率
     * @access protected
     * @return boolean
     */
    protected function createCache() {

        //获取当前model的缓存文件路径
        $cacheFile = $this->parseCacheFile();

        //获取数据表字段信息
        $tableInfo = $this->getTableInfo();

        $fields         = array();
        $primaryKey     = array();
        foreach ($tableInfo as $lines) {
            //获取主键信息
            if ($lines['Key'] == 'PRI') {
                $primaryKey[] = $lines['Field'];
            }
            //获取字段信息
            $fields[] = $lines['Field'];
        }
        $this->primaryKey = empty($primaryKey) ? '' : $primaryKey[0];
        $this->tableField = $fields;

        //缓存文件内容整理
        $cacheDataArray = array(
            'primaryKey'    => $this->primaryKey,
            'fields'        => $this->tableField,
        );

        $cacheContent = "<?php\r\nif (!defined('IN_DOIT')) exit();\r\nreturn " . var_export($cacheDataArray, true) . ";";

        //分析model缓存文件目录
        if (!is_dir($this->cacheDir)) {
            //生成目录
            mkdir($this->cacheDir, 0777, true);
        } else if (!is_writable($this->cacheDir)) {
            //更改目录权限
            chmod($this->cacheDir, 0777);
        }

        //将缓存内容写入缓存文件
        file_put_contents($cacheFile, $cacheContent, LOCK_EX);

        return true;
    }

    /**
     * 加载当前model的缓存文件内容
     *
     * @access protected
     * @return array    缓存文件内容
     */
    protected function loadCache() {

        //分析model缓存文件名
        $cacheFile = $this->parseCacheFile();

        //分析缓存文件是否存在
        if (!is_file($cacheFile)) {
            return false;
        }

        $cacheDataArray       = include $cacheFile;
        $this->primaryKey     = $cacheDataArray['primaryKey'];
        $this->tableField     = $cacheDataArray['fields'];
        //清空不必要的内存占用
        unset($cacheDataArray);

        return true;
    }

    /**
     * 清空当前model的缓存文件
     *
     * @access protected
     * @return boolean
     */
    protected function clearCache() {

        //分析model缓存文件名
        $cacheFile = $this->parseCacheFile();

        //当model缓存文件存在时
        if (is_file($cacheFile)) {
            unlink($cacheFile);
        }

        return true;
    }

    /**
     * 分析当前model缓存文件的路径
     *
     * @access protected
     * @return string    缓存文件的路径
     */
    protected function parseCacheFile() {

        //获取数据表名
        $this->getTableName();

        return $this->cacheDir . $this->tableName . '_model.cache.data.php';
    }

    /**
     * 设置当前数据表的名称
     *
     * @access public
     * @param string $tableName 数据表名称
     * @return $this
     */
    public function setTableName($tableName) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        $this->tableName = trim($tableName);

        return $this;
    }

    /**
     * 设置数据表主键
     *
     * @access public
     * @param string $primaryKey 数据表主键
     * @return $this
     */
    public function setPrimaryKey($primaryKey) {

        //参数分析
        if (!$primaryKey) {
            return false;
        }

        $this->primaryKey = trim($primaryKey);

        return $this;
    }


    /**
     * 第二部分：SQL语句 组装（from, where, order by, limit, left join , group by, having, orwhere等）
     */


    /**
     * 组装SQL语句中的FROM语句
     *
     * 用于处理 SELECT fields FROM table之类的SQL语句部分
     * @access public
     * @param mixed $tableName  所要查询的数据表名，参数支持数组
     * @param mixed $columns       所要查询的数据表字段，参数支持数组，默认为null, 即数据表全部字段
     * @return $this
     *
     * @example
     * $model = new DemoModel();
     *
     * 法一：
     * $model->from('数据表名', array('id', 'name', 'age'));
     *
     * 法二：
     * $model->from('数据表名'); //当第二参数为空时，默认为全部字段
     *
     * 法三:
     * $model->from(array('p'=>'product', 'i'=>'info'), array('p.id', 'p.name', 'i.value'));
     *
     * 法四：
     * $model->from('list', array('total_num'=>'count(id)')); //第二参数支持使用SQL函数，目前支持，count(),sum(),avg(),max(),min(), distinct()等
     *
     * 法五：
     * $model->from();
     *
     */
    public function from($tableName = null, $fields = null) {

        //参数分析
        if (!$tableName) {
            $this->getTableName();
            $tableStr = $this->tableName;
        }

        //对数据表名称的分析
        if ($tableName && is_array($tableName)) {

            $optionArray = array();
            foreach ($tableName as $key=>$value) {
                //当有数据表前缀时
                if (!empty($this->prefix)) {
                    $optionArray[] = is_int($key) ? ' ' . $this->prefix . trim($value) : ' ' . $this->prefix . trim($value) . ' AS ' . $key;
                } else {
                    $optionArray[] = is_int($key) ? ' ' . trim($value) : ' ' . trim($value) . ' AS ' . $key;
                }
            }
            $tableStr = implode(',', $optionArray);
            //清空不必要的内存占用
            unset($optionArray);

        } else {

            if (!$tableStr) {
                $tableStr = (!empty($this->prefix)) ? $this->prefix . trim($tableName) : trim($tableName);
            }
        }

        //对数据表字段的分析
        $itemStr = $this->_parseFields($fields);

        //组装SQL中的FROM片段
        $this->_parts['from'] = 'SELECT ' . $itemStr . ' FROM ' . $tableStr;

        return $this;
    }

    /**
     * 分析数据表字段信息
     *
     * @access     protected
     * @param    array    $fields    数据表字段信息.本参数为数组
     * @return     string
     */
    protected static function _parseFields($fields = null) {

        if (is_null($fields)) {
            return '*';
        }

        if(is_array($fields)) {
            $fieldsArray = array();
            foreach($fields as $key=>$value) {
                $fieldsArray[] = is_int($key) ? $value : $value . ' AS ' . $key;
            }
            $fieldsStr = implode(',', $fieldsArray);
            //清空不必要的内存占用
            unset($fieldsArray);
        } else {
            $fieldsStr = $fields;
        }

        return $fieldsStr;
    }

    /**
     * 组装SQL语句的WHERE语句
     *
     * 用于处理 WHERE id=3721 诸如此类的SQL语句部分
     * @access public
     * @param string $where WHERE的条件
     * @param string $value 数据参数，一般为字符或字符串
     * @return $this
     *
     * @example
     * $model = new DemoModel();
     *
     * 法一：
     * $model->where('id=23');
     *
     * 法二:
     * $model->where('name=?', 'doitphp');
     *
     * 法三:
     * $model->where(array('class=3', 'age>21', 'no=10057'));
     *
     * 法四:
     * $model->where('class=3')->where('age>21')->where('no=10057');
     *
     * 法五:
     * $model->where(array('id>5', 'name=?'), 'tommy');
     *
     * 法六:
     * $model->where(array('name=?', 'group=?'), 'tommy', 5);
     *
     * 法七:
     * $model->where(array('name=?', 'group=?', 'title like ?'), array('tommy', 5, 'doitphp%'));
     *
     */
    public function where($where, $value = null) {

        return $this->_where($where, $value, true);
    }

    /**
     * 组装SQL语句的ORWHERE语句
     *
     * 用于处理 ORWHERE id=2011 诸如此类的SQL语句部分
     * @access public
     * @param string $where WHERE的条件
     * @param string $value 数据参数，一般为字符或字符串
     * @return $this
     *
     * @example
     * 使用方法与$this->where()类似
     */
    public function orwhere($where, $value = null) {

        return $this->_where($where, $value, false);
    }

    /**
     * 组装SQL语句中WHERE及ORWHERE语句
     *
     * 本方法用来为方法where()及orwhere()提供"配件"
     * @access protected
     * @param string $where SQL中的WHERE语句中的条件.
     * @param string $value 数值（数据表某字段所对应的数据，通常都为字符串或字符）
     * @param boolean $isWhere 注:为true时是为where()， 反之 为orwhere()
     * @return $this
     */
    protected function _where($where, $value = null, $isWhere = true) {

        //参数分析
        if (!$where) {
            return false;
        }

        //当参数为关联数组时
        if(Controller::is_assoc($where)){
        	$whereArray = array();
        	$where = $this->quoteInto($where);
        	foreach ($where as $k => $v){
        		if(is_array($v)){
        			$whereArray [] = $k . ' IN (' . implode(',', $v) . ')';
        		}else{
        			$whereArray [] = $k . '=' . $v;
        		}
        	}
        	$where = implode(' AND ', $whereArray);
        	unset($whereArray);
        }else if (is_array($where)) {
        	//分析参数条件，当参数为列表数组时
            $whereArray = array();
            foreach ($where as $string) {
                $whereArray[] = trim($string);
            }

            $where = implode(' AND ', $whereArray);
            unset($whereArray);
        }

        //当$model->where('name=?', 'tommy');操作时
        if (!is_null($value)) {
            if (!is_array($value)) {
                $value = $this->quoteInto($value);
                $where = str_replace('?', $value, $where);
            } else {
                $where = $this->prepare($where, $value);
            }
        }

        //处理where或orwhere.
        if ($isWhere == true) {
            $this->_parts['where']        = ($this->_parts['where']) ? $this->_parts['where'] . ' AND ' . $where : ' WHERE ' . $where;
        } else {
            $this->_parts['or_where']     = ($this->_parts['or_where']) ? $this->_parts['or_where'] . ' AND ' . $where : ' OR ' . $where;
        }

        return $this;
    }

    /**
     * 组装SQL语句排序(ORDER BY)语句
     *
     * 用于处理 ORDER BY post_id ASC 诸如之类的SQL语句部分
     * @access public
     * @param mixed $string 排序条件。注：本参数支持数组
     * @return $this
     */
    public function order($string) {

        //参数分析
        if (!$string) {
            return false;
        }

        //当参数为数组时
        if (is_array($string)) {
            $orderArray = array();
            foreach ($string as $lines) {
                $orderArray[] = trim($lines);
            }
            $string = implode(',', $orderArray);
            unset($orderArray);
        }

        $string = trim($string);
        $this->_parts['order'] = ($this->_parts['order']) ? $this->_parts['order'] . ', ' . $string : ' ORDER BY ' . $string;

        return $this;
    }

    /**
     * 组装SQL语句LIMIT语句
     *
     * limit(10,20)用于处理LIMIT 10, 20之类的SQL语句部分
     * @access public
     * @param int $offset 启始id, 注:参数为整形
     * @param int $count  显示的行数
     * @return $this
     */
    public function limit($offset, $count = null) {

        //参数分析
        $offset     = (int)$offset;
        $count      = (int)$count;

        $limitStr   = !empty($count) ? $offset . ', ' . $count : $offset;
        $this->_parts['limit'] = ' LIMIT ' . $limitStr;

        return $this;
    }

    /**
     * 组装SQL语句的LIMIT语句
     *
     * 注:本方法与$this->limit()功能相类，区别在于:本方法便于分页,参数不同
     * @access public
     * @param int $page     当前的页数
     * @param int $count     每页显示的数据行数
     * @return $this
     */
    public function pageLimit($page, $count) {

        $startId = (int)$count * ((int)$page - 1);

        return $this->limit($startId, $count);
    }

    /**
     * 组装SQL语句中LEFT JOIN语句
     *
     * jion('表名2', '关系语句')相当于SQL语句中LEFT JOIN 表2 ON 关系SQL语句部分
     * @access public
     * @param string $tableName    数据表名，注：本参数支持数组，主要用于数据表的alias别名
     * @param string $where            join条件，注：不支持数组
     * @return $this
     */
    public function join($tableName, $where) {

        //参数分析
        if (!$tableName || !$where) {
            return false;
        }

        //处理数据表名
        if (is_array($tableName)) {
            foreach ($tableName as $key=>$string) {
                if (!empty($this->prefix)) {
                    $tableNameStr = is_int($key) ? $this->prefix . trim($string) : $this->prefix . trim($string) . ' AS ' . $key;
                } else {
                    $tableNameStr = is_int($key) ? trim($string) :  trim($string) . ' AS ' . $key;
                }
                //数据处理，只处理一个数组元素
                break;
            }
        } else {
            $tableNameStr = (!empty($this->prefix)) ? $this->prefix . trim($tableName) : trim($tableName);
        }

        //处理条件语句
        $where = trim($where);
        $this->_parts['join'] = ' LEFT JOIN ' . $tableNameStr . ' ON ' . $where;

        return $this;
    }

    /**
     * 组装SQL的GROUP BY语句
     *
     * 用于处理SQL语句中GROUP BY语句部分
     * @access public
     * @param mixed $groupName    所要排序的字段对象
     * @return $this
     */
    public function group($groupName) {

        //参数分析
        if (!$groupName) {
            return false;
        }

        if (is_array($groupName)) {
            $groupArray = array();
            foreach ($groupName as $lines) {
                $groupArray[] = trim($lines);
            }
            $groupName = implode(',', $groupArray);
            unset($groupArray);
        }

        $this->_parts['group'] = ($this->_parts['group']) ? $this->_parts['group'] . ', ' . $groupName : ' GROUP BY ' . $groupName;

        return $this;
    }

    /**
     * 组装SQL的HAVING语句
     *
     * 用于处理 having id=2011 诸如此类的SQL语句部分
     * @access pulbic
     * @param string|array $where 条件语句
     * @param string $value    数据表某字段的数据值
     * @return $this
     *
     * @example
     * 用法与where()相似
     */
    public function having($where, $value = null) {

        return $this->_having($where, $value, true);
    }

    /**
     * 组装SQL的ORHAVING语句
     *
     * 用于处理or having id=2011 诸如此类的SQL语句部分
     * @access pulbic
     * @param string|array $where 条件语句
     * @param string $value    数据表某字段的数据值
     * @return $this
     *
     * @example
     * 用法与where()相似
     */
    public function orhaving($where, $value = null) {

        return $this->_having($where, $value, false);
    }

    /**
     * 组装SQL的HAVING,ORHAVING语句
     *
     * 为having()及orhaving()方法的执行提供'配件'
     * @access protected
     * @param mixed $where 条件语句
     * @param string $value    数据表某字段的数据值
     * @param boolean $isHaving 当参数为true时，处理having()，当为false时，则为orhaving()
     * @return $this
     */
    protected function _having($where, $value = null, $isHaving = true) {

        //参数分析
        if (!$where) {
            return false;
        }

        //分析参数条件，当参数为数组时
        if (is_array($where)) {
            $whereArray = array();
            foreach ($where as $string) {
                $whereArray[] = trim($string);
            }

            $where = implode(' AND ', $whereArray);
            unset($whereArray);
        }

        //当程序$model->where('name=?', 'tommy');操作时
        if (!is_null($value)) {
            if (!is_array($value)) {
                $value = $this->quoteInto($value);
                $where = str_replace('?', $value, $where);
            } else {
                $where = $this->prepare($where, $value);
            }
        }

        //分析having() 或 orhaving()
        if ($isHaving == true) {
            $this->_parts['having']     = ($this->_parts['having']) ? $this->_parts['having'] . ' AND ' . $where : ' HAVING ' . $where;
        } else {
            $this->_parts['or_having']  = ($this->_parts['or_having']) ? $this->_parts['or_having'] . ' AND ' . $where : ' OR ' . $where;
        }

        return $this;
    }

    /**
     * 执行SQL语句中的SELECT查询语句
     *
     * 组装SQL语句并完成查询，并返回查询结果，返回结果可以是多行，也可以是单行
     * @access public
     * @param boolean $allLines 是否输出为多行数据，默认为true,即多行数据；当false时输出的为单行数据
     * @return array
     */
    public function select($allLines = true) {

        //分析查询数据表的语句
        if (!$this->_parts['from']) {
            return false;
        }

        //组装完整的SQL查询语句
        $partsNameArray = array('from', 'join', 'where', 'or_where', 'group', 'having', 'or_having', 'order', 'limit');
        $sqlStr = '';
        foreach ($partsNameArray as $partName) {
            if ($this->_parts[$partName]) {
                $sqlStr .= $this->_parts[$partName];
                unset($this->_parts[$partName]);
            }
        }
        return ($allLines == true) ? $this->db->getArray($sqlStr) : $this->db->fetchRow($sqlStr);
    }

    /**
     * 字符串转义函数
     *
     * SQL语句指令安全过滤,用于字符转义
     * @access public
     * @param mixed $value 所要转义的字符或字符串,注：参数支持数组
     * @return string|string
     */
    public function quoteInto($value = null) {

        //参数判断
        if (is_null($value)) {
            return false;
        }

        //参数是否为数组
        if (is_array($value)) {
            foreach ($value as $key=>$string) {
                $value[$key] = $this->quoteInto($string);
            }
        } else {
            //当参数为字符串或字符时
            if (is_string($value)) {
                $value = '\'' . $this->db->escapeString($value) . '\'';
            }
        }

        return $value;
    }

    /**
     * 获取数据的总行数
     *
     * @access public
     * @param string $tableName    所要查询的数据表名
     * @param string $fieldName    所要查询字段名称
     * @param string $where         查询条件
     * @param string $value         数值（数据表某字段所对应的数据，通常都为字符串或字符）
     * @return integer
     *
     * @example
     *
     * $model = new Model();
     *
     * 法一:
     * $mdoel->count('tableName', 'title', 'id=1');
     *
     * 法二:
     * $mdoel->count('tableName', 'title', 'name=?', 'tommy');
     *
     * 法三:
     * $mdoel->count('tableName', 'title', array('name=?', 'title like ?'), array('tommy', 'doitphp'));
     */
    public function count($tableName = null, $fieldName = null, $where = null, $value = null) {

        if (!$fieldName) {
            $this->getPrimaryKey();
            $fieldName = $this->primaryKey;
        }

        $data = (!is_null($where)) ? $this->from($tableName, array('total_num'=>'count(' . $fieldName . ')'))->where($where, $value)->select(false) : $this->from($tableName, array('total_num'=>'count(' . $fieldName . ')'))->select(false);

        return $data['total_num'];
    }


    /**
     * 第三部分：对数据表的查询，更改，写入，删除操作。注：此函数均为数组型数据操作，非面向对象操作
     */


    /**
     * 对数据表的主键查询
     *
     * 根据主键，获取某个主键的一行信息,主键可以类内设置。默认主键为数据表的物理主键
     * 如果数据表没有主键，可以在model中定义
     * @access public
     * @param int|string|array $id 所要查询的主键值.注：本参数支持数组，当参数为数组时，可以查询多行数据
     * @param array    $fields     返回数据的有效字段(数据表字段)
     * @return string              所要查询数据信息（单行或多行）
     *
     * @example
     *
     * 实例化model
     * $model = new DemoModel();
     *
     * $model->find(1024);
     *
     * 自定义model的主键
     * $model->primaryKey = 'memeber_name'; //数据类型并非int，也可以是varchar,char等类的,不过当主键数据类型为varchar,char时，参数要使用$this->quoteInto()进行转义。
     *
     * $name = $this->quoteInto('tommy');
     * $model->find($name);
     *
     */
    public function find($id, $fields = null) {

        //参数分析
        if (!$id) {
            return false;
        }

        //获取主键及数据表名
        $this->getTableName();
        $this->getPrimaryKey();

        //分析查询的字段信息
        $fieldsStr   = $this->_parseFields($fields);
        $sqlStr      = 'SELECT ' . $fieldsStr . ' FROM ' . $this->tableName . ' WHERE ' . $this->primaryKey;

        if (is_array($id)) {
            $sqlStr .= ' IN (\'' . implode('\',\'', $id) . '\')';
            $myrow   = $this->db->getArray($sqlStr);
        } else{
            $sqlStr .= '=\'' . trim($id) . '\'';
            $myrow   = $this->db->fetchRow($sqlStr);
        }

        return $myrow;
    }

    /**
     * 获取数据表的全部数据信息
     *
     * 以主键为中心排序，获取数据表全部数据信息. 注:如果数据表数据量较大时，慎用此函数，以免数据表数据量过大，造成数据库服务器内存溢出,甚至服务器宕机
     * @access public
     * @param array        $fields    返回的数据表字段,默认为全部.即SELECT * FROM tableName
     * @param  boolean    $orderAsc  数据排序,若为true时为ASC,为false时为DESC, 默认为ASC
     * @param integer    $offset      limit启起ID
     * @param integer    $count       显示的行数
     * @return array                  数据表数据信息
     */
    public function findAll($fields = null, $orderAsc = true, $offset = null, $count = null) {

        //获取主键及数据表名
        $this->getTableName();
        $this->getPrimaryKey();

        //分析查询的字段信息
        $fieldsStr = $this->_parseFields($fields);

        $sqlStr  = 'SELECT ' . $fieldsStr . ' FROM ' . $this->tableName . ' ORDER BY ' . $this->primaryKey . (($orderAsc == true) ? ' ASC' : ' DESC');
        if (!is_null($offset)) {
            $this->_parts['limit'] = '';
            $this->limit($offset, $count);
            $sqlStr .= $this->_parts['limit'];
            unset($this->_parts['limit']);
        }

        return $this->db->getArray($sqlStr);
    }

    /**
     * 查询数据表单行数据
     *
     * 根据一个查询条件，获取一行数据，返回数据为数组型，索引为数据表字段名
     * @access public
     * @param mixed     $where 查询条件
     * @param sring      $value 数值
     * @param array        $fields    返回数据的数据表字段,默认为全部字段.注：本参数为数组
     * @return array     所要查询的数据表数据
     *
     * @example
     *
     * 法一：
     * $data = $model->getOne('name=?', 'tommy');
     *
     * 法二：
     * $data = $model->getOne(array('age>23', 'class=4'));
     *
     * 法三：
     * $data = $model->getOne('name=?', 'tommy', array('name', 'age', 'addr'));
     *
     * 法四:
     * $data = $model->getOne(array('name=?', 'title like ?'), array('tommy', 'doitphp%'), array('name', 'age', 'addr'));
     */
    public function getOne($where, $value = null, $fields = null) {

        //参数分析
        if (!$where) {
            return false;
        }

        //获取数据表名
        $this->getTableName();

        //分析查询的字段信息
        $fieldsStr = $this->_parseFields($fields);

        //处理查询的SQL语句
        $this->_parts['where'] = '';
        $this->where($where, $value);
        $whereStr  = $this->_parts['where'];
        unset($this->_parts['where']);

        $sqlStr = 'SELECT ' . $fieldsStr . ' FROM ' . $this->tableName . $whereStr;

        return $this->db->fetchRow($sqlStr);
    }

    /**
     * 查询数据表多行数据
     *
     * 根据一个查询条件，获取多行数据。并且支持数据排序
     * @access public
     * @param mixed    $where 查询条件
     * @param sring    $value 数值
     * @param array $fields    返回数据的数据表字段.默认为全部字段.注:本参数为数组
     * @param mixed $order 排序条件
     * @param integer    $offset    limit启起ID
     * @param integer    $count    显示的行数
     * @return array
     *
     * @example
     * $model = new Model();
     *
     * 法一:
     * $data = $model->getAll('name=?', 'doitphp, array('id', 'name', 'content'));
     *
     * 法二:
     * $data = $model->getAll(array('author=?', 'title liek ?'), array('tommy', 'doitphp%'));
     *
     * 法三:
     * $data = $model->getAll('post_id>10', null, array('id', 'title'), 'post_id desc', 10, 20);
     *
     * 法四:
     * $data = $model->getAll('post_id>?', 10, array('id', 'title'), 'post_id desc', 10, 20);
     *
     */
    public function getAll($where, $value=null, $fields = null, $order = null, $offset = null, $count = null) {

        //参数分析
        if (!$where) {
            return false;
        }

        //获取数据表名
        $this->getTableName();

        //分析查询的字段信息
        $fieldsStr = $this->_parseFields($fields);
        $sqlStr    = 'SELECT ' . $fieldsStr . ' FROM ' . $this->tableName;

        //处理查询的SQL语句
        $this->_parts['where'] = '';
        $this->where($where, $value);
        $sqlStr .= $this->_parts['where'];
        unset($this->_parts['where']);

        //处理排序的SQL语句
        if (!is_null($order)) {
            $this->_parts['order'] = '';
            $this->order($order);
            $sqlStr .= $this->_parts['order'];
            unset($this->_parts['order']);
        }

        //处理limit语句
        if (!is_null($offset)) {
            $this->_parts['limit'] = '';
            $this->limit($offset, $count);
            $sqlStr .= $this->_parts['limit'];
            unset($this->_parts['limit']);
        }

        return $this->db->getArray($sqlStr);
    }

    /**
     * 数据表写入操作
     *
     * 向当前model对应的数据表插入数据
     * @access public
     * @param array $data 所要写入的数据内容。注：数据必须为数组
     * @param boolean $returnInsertId 是否返回数据为last insert id
     * @return boolean
     *
     * @example
     *
     * $data = array('name'=>'tommy', 'age'=>23, 'addr'=>'山东'); //注：数组的键值是数据表的字段名
     *
     * $model->insert($data);
     */
    public function insert($data, $returnInsertId = false) {

        //参数分析
        if (!$data || !is_array($data)) {
            return false;
        }

        //获取数据表名及字段信息
        $this->getTableName();
        $this->getTableFields();

        //处理数据表字段与数据的对应关系
        $fieldArray     = array();
        $contentArray   = array();

        foreach ($data as $key=>$value) {

            if (in_array($key, $this->tableField)) {
                $fieldArray[]   = trim($key);
                $contentArray[] = '\'' . $this->db->escapeString(trim($value)) . '\'';
            }
        }
        $fieldStr      = implode(',', $fieldArray);
        $contentStr    = implode(',', $contentArray);
        //清空不必要的内存占用
        unset($fieldArray, $contentArray);

        $sqlStr = 'INSERT INTO ' . $this->tableName . ' (' . $fieldStr . ') VALUES (' . $contentStr . ')';

        $result = $this->db->query($sqlStr);
        //返回last insert id
        if ($result && $returnInsertId === true) {
            return $this->getInsertId();
        }
        return $result;
    }

    /**
     * 数据表更改操作
     *
     * 更改当前model所对应的数据表的数据内容
     * @access public
     * @param array     $data 所要更改的数据内容
     * @param mixed        $where 更改数据所要满足的条件
     * @param string    $$params 数值，对满足更改的条件的进一步补充
     * @return boolean
     *
     * @example
     *
     * $update_array = array('title'=>'This is title', 'content'=>'long long ago...');
     *
     * 法一:
     * $model->update($update_array, 'poste_id=12');
     *
     * 法二:
     * $model->update($update_array, 'name=?', 'tommy');
     *
     * 法三:
     * $model->update($update_array, array('name=?', 'content like ?'), array('tommy', 'doitphp%'));
     */
    public function update($data, $where, $params = null) {

        //参数分析
        if (!is_array($data) || !$data || !$where) {
            return false;
        }
        //获取数据表名及字段信息
        $this->getTableName();
        $this->getTableFields();

        $contentArray = array();
        foreach ($data as $key=>$value) {
            if (in_array($key, $this->tableField)) {
                $contentArray[] = $key . ' = \'' . $this->db->escapeString(trim($value)) . '\'';
            }
        }
        $contentStr = implode(',', $contentArray);
        unset($contentArray);
        //组装SQL语句
        $sqlStr = 'UPDATE ' . $this->tableName . ' SET ' . $contentStr;

        //条件查询SQL语句的处理
        $this->_parts['where'] = '';
        $this->where($where, $params);
        $sqlStr .= $this->_parts['where'];
        unset($this->_parts['where']);

        return $this->db->query($sqlStr);
    }

    /**
     * 数据表删除操作
     *
     * 从当前model所对应的数据表中删除满足一定查询条件的数据内容
     * @access public
     * @param  mixed     $where 所要满足的条件
     * @param  sring    $value 数值，对满足条件的进一步补充
     * @return boolean
     *
     * @example
     *
     * $model = new Model();
     *
     * 法一:
     * $model->delete('post_id=23');
     *
     * 法二:
     * $model->delete('post_id=?', 23);
     *
     * 法三:
     * $model->delete(array('name=?', 'content like ?'), array('tommy', 'doitphp%'));
     */
    public function delete($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }

        //获取数据表名及字段信息
        $this->getTableName();

        $sqlStr = 'DELETE FROM ' . $this->tableName;

        //处理SQL的条件查询语句
        $this->_parts['where'] = '';
        $this->where($where, $value);
        $sqlStr .= $this->_parts['where'];
        unset($this->_parts['where']);

        return $this->db->query($sqlStr);
    }


    /**
     * 第四部分：其它操作，包括执行SQL语句查询操作，事务处理等
     */


    /**
     * 执行SQL查询语句
     *
     * 当SQL语句为查询语句时返回执行后的全部数据
     * @access public
     * @param string $sql SQL语句
     * @param boolean $allLines 是否显示全部数据开关，当为true时，显示全部数据，为false时，显示一行数据，默认为true
     * @param boolean $isSelect 是否为查询语句
     * @return array | void
     */
    public function execute($sql, $allLines = true, $isSelect = false) {

        //参数分析
        if (!$sql) {
            return false;
        }
        $sql = trim($sql);

        //分析数据表前缀
        $sql = str_replace('#__', $this->prefix, $sql);

        //SQL语句为查询语句时
        if ($isSelect == true || strtolower(substr($sql, 0, 6)) == 'select') {
            return ($allLines == true) ? $this->db->getArray($sql) : $this->db->fetchRow($sql);
        }

        return $this->db->query($sql);
    }

    /**
     * SQL语句的转义
     *
     * 完成SQL语句中关于数据值字符串的转义
     * @access public
     * @param string $sql SQL语句
     * @return string
     *
     * @example
     * $sql = 'SELECT * FROM `demo` WHERE `id` =? AND `name`=?';
     *
     * $sql = $model->prepare($sql, 23, 'tommy');
     * 或
     * $sql = $model->prepare($sql, array(23, 'tommy'));
     */
    public function prepare($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //获取自身函数的参数
        $args = func_get_args();
        //弹出数据第一个元素，保留后面的参数
        array_shift($args);

        //如果$args的第一个元素为数组,移除其它元素
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        //当$args为空时,直接返回,不对SQL语句进行任何的转义
        if (!$args) {
            return $sql;
        }

        //替换SQL语句中的转义符
        $sql = str_replace('?', '%s', $sql);
        //转义参数值
        $args = $this->quoteInto($args);

        return vsprintf($sql, $args);
    }

    /**
     * 获取insert_id操作
     *
     * 获取数据表上次写入操作时的insert_id
     * @access public
     * @return int    insert_id
     */
    public function getInsertId() {

        return $this->db->insertId();
    }

    /**
     * 实例化model
     *
     * 用于自定义业务逻辑时,实例化其它数据表的model
     * @param string $modelName 数据表名
     * @return object
     */
    public static function model($modelName) {

        if (!$modelName) {
            return false;
        }

        return Controller::model($modelName);
    }

    /**
     * 优雅输出print_r()函数所要输出的内容
     *
     * 用于程序调试时,完美输出调试数据,功能相当于print_r().当第二参数为true时(默认为:false),功能相当于var_dump()。
     * 注:本方法一般用于程序调试
     * @access public
     * @param array $data         所要输出的数据
     * @param boolean $option     选项:true或 false
     * @return array            所要输出的数组内容
     */
    public function dump($data, $option = false) {

        return Controller::dump($data, $option);
    }

    /**
     * 开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        return $this->db->startTrans();
    }

    /**
     * 提交事务处理
     *
     * @access public
     * @return boolean
     */
    public function commit() {

        return $this->db->commit();
    }

    /**
     * 事务回滚
     *
     * @access public
     * @return boolean
     */
    public function rollback() {

        return $this->db->rollback();
    }

    /**
     * 自动变量设置
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的赋值 。
     *
     * @access public
     * @param string $name 属性名
     * @param string $value 属性值
     * @return void
     */
     public function __set($name, $value) {

         //允许model对数据表名，数据表主键的自定义
         if (in_array($name, array('tableName', 'primaryKey'))) {
             $this->$name = $this->db->escapeString($value);
         }

         return true;
     }

     /**
      * 输出类的实例化对象
      *
      * 直接调用函数,输出内容,通常用来输出组装的SQL查询语句
      * @access public
      * @return string
      */
     public function __toString() {

         if ($this->_parts) {

             $partsNameArray = array('from', 'join', 'where', 'or_where', 'group', 'having', 'or_having', 'order', 'limit');

             $sqlStr = '';
            foreach ($partsNameArray as $partName) {
                if ($this->_parts[$partName]) {
                    $sqlStr .= $this->_parts[$partName];
                    unset($this->_parts[$partName]);
                }
            }

            return (string)$sqlStr;
         }

         return (string)'This is ' . get_class($this) . ' Class!';
     }

     /**
      * 获取错误信息
      *
      * @access public
      * @return string
      */
     public function getErrorInfo() {

     	 if(method_exists($this->db, 'error')) {
     	 	return $this->db->error();
     	 } else {
     	 	return '';
     	 }
     }

     /**
      * 获取错误码
      * 
      */
     public function getErrorNo(){
     	if(method_exists($this->db, 'errno')) {
     		return $this->db->errno;
     	}
     	return 0;
     }
     
    /**
     * 析构函数
     *
     * 当本类程序运行结束后，用于"打扫战场"，如：清空无效的内存占用等
     * @access public
     * @return void
     */
    public function __destruct() {
        //清空不必要的内存占用
        $unsetArray = array($this->_parts, $this->myrow);
        foreach ($unsetArray as $name) {
            //当变量存在时
            if (isset($name)) {
                unset($name);
            }
        }
    }

    /**
     * 获取数据库的连接参数
     *
     * @access protected
     * @return array
     */
    protected function getConfig() {

        return Controller::getConfig('db');
    }

    /**
     * 工厂模式实例化数据库驱动操作
     *
     * 实现不同数据库驱动的实例化,如果参数中没有driver（数据库类型）,默认为mysqli驱动。
     * @access public
     * @param array $params 数据库配置信息（数据库的连接参数）
     * @return object
     */
    public static function factory($params) {

        switch ($params['driver']) {

            case 'mysqli':
                $linkId = db_mysqli::getInstance($params);
                break;

            case 'mysql':
                $linkId = db_mysql::getInstance($params);
                break;

            case 'pdo_mysql':
                //分析dsn信息
                if (!$params['dsn']) {
                    $dsnArray          = array();
                    $dsnArray['host']  =$params['host'];
                    $dsnArray['dbname']=$params['dbname'];

                    if (!empty($params['port'])){
                        $dsnArray['port']=$params['port'];
                    }
                    $params['dsn'] = sprintf('%s:%s', 'mysql', http_build_query($dsnArray, '', ';'));
                }
                //实例化pdo对象
                $linkId = db_pdo::getInstance($params);
                break;

            case 'oracle':
                $linkId = db_oracle::getInstance($params);
                break;

            case 'pdo_oracle':
                //分析dsn信息
                if (!$params['dsn']) {
                    if(!$params['port']) {
                        $params['port'] = 1521;
                    }
                    $params['dsn'] = $params['host'] ? 'oci:dbname=//' . $params['host'] . ':' . $params['port'] . '/' . $params['dbname'] : 'oci:dbname=' . $params['dbname'];
                }
                //实例化pdo对象
                $linkId = db_pdo::getInstance($params);
                break;

            case 'sqlite2':
                $linkId = db_sqlite::getInstance($params);
                break;

            case 'sqlite3':
                $params['driver'] = 'sqlite';
                $linkId = db_sqlite::getInstance($params);
                break;

            case 'postgresql':
                //检测参数信息是否完整
                if (!$params['host'] || !$params['username'] || !$params['password'] || !$params['dbname']) {
                    Log::error('Database Server HostName or UserName or Password or Database Name is error in the config file!');
                    EC::fail_page(EC_OTH);
                }
                $linkId  = db_postgres::getInstance($params);
                break;

            case 'pdo_postgresql':
                //分析dsn信息
                if (!$params['dsn']) {
                    $params['port']     = (!$params['port']) ? 5432 : $params['port'];
                    $params['dsn']      = 'pgsql:host=' . $params['host'] . ' port=' . $params['port'] . ' dbname=' . $params['dbname'] . ' user=' . $params['username'] . ' password=' . $params['password'];
                }
                $linkId = db_pdo::getInstance($params);
                break;

            case 'mssql':
                $linkId = db_mssql::getInstance($params);
                break;

            case 'pdo':
                $linkId = db_pdo::getInstance($params);
                break;

            default:
                $linkId = db_mysqli::getInstance($params);
        }

        return $linkId;
    }
}