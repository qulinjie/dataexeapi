<?php
/**
 * db_mysqli.class.php
 *
 * db_mysqli数据库驱动,完成对mysql数据库的操作
 * @package core
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id:db_mysqli.class.php 1.3 2011-11-13 20:37:00Z tommy $
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class db_mysqli extends Base {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    public static $_instance;

    /**
     * 数据库连接ID
     *
     * @var object
     */
    public $dbLink;

    /**
     * 事务处理开启状态
     *
     * @var boolean
     */
    public $Transactions;


    /**
     * 构造函数
     *
     * 用于初始化运行环境,或对基本变量进行赋值
     * @access public
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     * @return boolean
     */
    public function __construct($params = array()) {

        //检测参数信息是否完整
        if (!$params['host'] || !$params['username'] || !$params['dbname']) {
            Controller::halt('Database Server:HostName or UserName or Password or Database Name is error in the config file!');
        }

        //实例化mysql连接ID
        $this->dbLink = $params['port'] ? @new mysqli($params['host'], $params['username'], $params['password'], $params['dbname'], $params['port']) : @new mysqli($params['host'], $params['username'], $params['password'], $params['dbname']);

        if (mysqli_connect_errno()) {
            Log::error('connect to mysql error:' . var_export($params, true));
            return false;
        } else {
            //设置数据库编码
            $this->dbLink->query("SET NAMES {$params['charset']}");

            $sqlVersion = $this->getServerInfo();
            if (version_compare($sqlVersion,'5.0.2','>=')) {
                $this->dbLink->query("SET SESSION SQL_MODE=''");
            }
        }

        return true;
    }

    /**
     * 执行SQL语句
     *
     * SQL语句执行函数.
     * @access public
     * @param string $sql SQL语句内容
     * @return mixed
     */
    public function query($sql) {

    	Log::notice('sql:' . $sql);
    	
    	//Log::write($sql, 'debug', 'sql-'.date('Y-m-d'));
        
        //参数分析
        if (!$sql) {
            return false;
        }

        //获取执行结果
        $result = $this->dbLink->query($sql);

        if(false === $result){
        	Log::error('sql error: ' . $sql . ' errno:' . $this->errno() . ' error:' . $this->error());
        }
        
        return $result;
    }

    /**
     * 获取mysql数据库服务器信息
     *
     * @access public
     * @return string
     */
    public function getServerInfo() {
        //当没有mysql连接时
        if (!$this->dbLink) {
            return false;
        }

        return $this->dbLink->server_version;
    }

    /**
     * 获取mysql错误描述信息
     *
     * @access public
     * @return string
     */
    public function error() {

        return $this->dbLink->error;
    }

    /**
     * 获取mysql错误信息代码
     *
     * @access public
     * @return int
     */
    public function errno() {

        return $this->dbLink->errno;
    }

    /**
     * 通过一个SQL语句获取一行信息(字段型)
     *
     * @access public
     * @param string $sql SQL语句内容
     * @return mixed
     */
    public function fetchRow($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //执行SQL语句
        $result = $this->query($sql);

        if(!$result){
            return false;
        }

        $rows = $result->fetch_assoc();
        //清空不必要的内存占用
        $result->free();

        return $rows;
    }

    /**
     * 通过一个SQL语句获取全部信息(字段型)
     *
     * @access public
     * @param string $sql SQL语句
     * @return array
     */
    public function getArray($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //执行SQL语句.
        $result = $this->query($sql);

        if (!$result) {
            return false;
        }

        $myrow = array();
        while ($row = $result->fetch_assoc()) {
            $myrow[] = $row;
        }
        $result->free();

        return $myrow;
    }

    /**
     * 获取insert_id
     *
     * @access public
     * @return int
     */
    public function insertId(){

        return ($id = $this->dbLink->insert_id) >= 0 ? $id :$this->query("SELECT last_insert_id()")->fetch_row();
    }

    /**
     * 开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        if ($this->Transactions == false) {
            $this->dbLink->autocommit(false);
            $this->Transactions = true;
        }

        return true;
    }

    /**
     * 提交事务处理
     *
     * @access public
     * @return boolean
     */
    public function commit() {

        if($this->Transactions == true){
            $result = $this->dbLink->commit();

            if ($result) {
                $this->dbLink->autocommit(true);
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL Commit failed <br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL Commit failed. Error Code:' . $this->errno() . ' Error Message:' . $this->error());
                    Controller::showMessage('Mysql Server SQL execute failed!');
                }
            }
        }

        return true;
    }

    /**
     * 事务回滚
     *
     * @access public
     * @return boolean
     */
    public function rollback() {

        if ($this->Transactions == true) {
            $result = $this->dbLink->rollback();

            if ($result) {
                $this->dbLink->autocommit(true);
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed! <br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed! Error Code:' . $this->errno() . ' Error Message:' . $this->error());
                    Controller::showMessage('Database SQL execute failed!');
                }
            }
        }

        return true;
    }

    /**
     * 转义字符
     *
     * @access public
     * @param string $string 待转义的字符串
     * @return string
     */
    public function escapeString($string = null) {

        //参数分析
        if (is_null($string)) {
            return  false;
        }
        return $this->dbLink->real_escape_string($string);
    }

    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        //关闭数据库连接
        if ($this->dbLink) {
            @$this->dbLink->close();
        }
    }

    /**
     * 单例模式
     *
     * 用于本类的单例模式(singleton)实例化
     * @access public
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     * @return object
     */
    public static function getInstance($params) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }
}