<?php
/**
 * db_pdo.class.php
 *
 * db_pdo数据库驱动,完成对oracle, sqllite, postgresql, mssql等数据库的操作
 * @package core
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id:db_pdo.class.php 1.3 2011-11-13 20:41:00Z tommy $
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class db_pdo extends Base {

    /**
     * 单例模式实例化对象
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

        //分析数据库连接信息
        if (!$params['dsn']) {
            return false;
        }

        //连接数据库
        $this->dbLink = @new PDO($params['dsn'], $params['username'], $params['password']);

        if (!$this->dbLink) {
            Controller::halt($params['driver'] . ' Server connect fail! <br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno(), 'Warning');
        }

        return true;
    }

    /**
     * 执行SQL语句
     *
     * SQL语句执行函数
     * @access public
     * @param string $sql SQL语句内容
     * @return mixed
     */
    public function query($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        $result = $this->dbLink->query($sql);

        //日志操作,当调试模式开启时,将所执行过的SQL写入SQL跟踪日志文件,便于DBA进行数据库优化.若调试模式关闭,当SQL语句执行错误时写入日志文件
        if (DOIT_DEBUG === false) {
            if ($result == false) {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                Log::write('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . ' Error Code:' . $this->errno() . 'Error Message:' . $this->error());
                Controller::showMessage('数据库SQL脚本执行错误!');
            }
        } else {
            //获取当前运行的controller及action名称
            $controllerId        = doit::getControllerName();
            $actionId            = doit::getActionName();
            $sqlLogFile         = 'trace/sql_' . date('Y_m_d', $_SERVER['REQUEST_TIME']);

            if ($result == true) {
                Log::write('[' . $controllerId . '][' . $actionId . ']:' . $sql, 'Normal', $sqlLogFile);
            } else {
                Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . '<br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno() . '<br/>Error SQL:' . $sql);
            }
        }

        return $result;
    }

    /**
     * 获取数据库错误描述信息
     *
     * @access public
     * @return string
     */
    public function error() {

        $info = $this->dbLink->errorInfo();

        return $info[2];
    }

    /**
     * 获取数据库错误信息代码
     *
     * @access public
     * @return int
     */
    public function errno() {

        return $this->dbLink->errorCode();
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

        $result = $this->query($sql);

        if (!$result) {
            return false;
        }

        $myrow  = $result->fetch(PDO::FETCH_ASSOC);
        $result = null;

        return $myrow;
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

        $result = $this->query($sql);

        if (!$result) {
            return false;
        }

        $myrow  = $result->fetchAll(PDO::FETCH_ASSOC);
        $result = null;

        return $myrow;
    }

    /**
     * 获取insert_id
     *
     * @access public
     * @return int
     */
    public function insertId() {

        return $this->dbLink->lastInsertId();
    }

    /**
     * 开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        if ($this->Transactions == false) {
            $this->dbLink->beginTransaction();
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

        if ($this->Transactions == true) {
            if ($this->dbLink->commit()) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL Commit failed <br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL Commit failed. Error Code:' . $this->errno() . ' Error Message:'.$this->error());
                    Controller::showMessage('Database SQL execute failed!');
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
            if ($this->dbLink->rollBack()) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed <br/>Error Message:' . $this->error() . '<br/>Error Code:' . $this->errno());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed. Error Code:' . $this->errno() . ' Error Message:' . $this->error());
                    Controller::showMessage('Database SQL execute failed!');
                }
            }
        }
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

        return trim($this->dbLink->quote($string), '\'');
    }

    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        if ($this->dbLink == true) {
            $this->dbLink = null;
        }
    }

    /**
     * 单例模式
     *
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