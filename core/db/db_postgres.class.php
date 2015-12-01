<?php
/**
 * db_postgres.class.php
 *
 * PostgreSql数据库驱动
 * @package core
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id:db_postgres.class.php 1.3 2011-11-13 20:41:00Z tommy $
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class db_postgres extends Base {

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
     * 最后执行的sql语句
     *
     * @var string
     */
    protected $lastQueryText;


    /**
     * 构造函数
     *
     * @access public
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     * @return boolean
     */
    public function __construct($params = array()) {

        //分析数据库连接信息
        $connect_string = '';
        $connect_string .= 'host=' . $params['host'] . ' ';
        if ($params['port']) {
            $connect_string .= 'port=' . $params['port'] . ' ';
        }

        $connect_string .= 'dbname=' . $params['dbname'] . ' ' . 'user=' . $params['username'] . ' ' . 'password=' . $params['password'];

        $this->dbLink = @pg_connect($connect_string);

        if (!$this->dbLink) {
            Controller::halt($params['driver'] . ' Server connect fail! <br/>Error Message:' . $this->error(), 'Warning');
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

        $result = pg_query($this->dbLink, $sql);

        //日志操作,当调试模式开启时,将所执行过的SQL写入SQL跟踪日志文件,便于DBA进行数据库优化.若调试模式关闭,当SQL语句执行错误时写入日志文件
        if (DOIT_DEBUG === false) {
            if ($result == false) {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                Log::write('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . ' Error Message:' . $this->error());
                Controller::showMessage('Database SQL execute failed!');
            } else {
                $this->lastQueryText = $sql;
            }
        } else {
            //获取当前运行的controller及action名称
            $controllerId        = doit::getControllerName();
            $actionId            = doit::getActionName();
            $sqlLogFile         = 'trace/sql_' . date('Y_m_d', $_SERVER['REQUEST_TIME']) . '. log';

            if ($result == true) {
                $this->lastQueryText = $sql;
                Log::write('[' . $controllerId . '][' . $actionId . ']:' . $sql, 'Normal', $sqlLogFile);
            } else {
                Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . '<br/>Error Message:' . $this->error(). '<br/>Error SQL:' . $sql);
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

        $error = ($this->dbLink) ? pg_last_error($this->dbLink) :pg_last_error();

        return $error['message'];
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

        $myrow     = pg_fetch_assoc($result);
        pg_free_result($result);

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

        $myrow     = array();
        while ($row = pg_fetch_assoc($result)) {
            $myrow[] = $row;
        }
        pg_free_result($result);

        return $myrow;
    }

    /**
     * 获取insert_id
     *
     * @access public
     * @return int
     */
    public function insertId(){

        if ($this->lastQueryText != '') {
            if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->lastQueryText, $tablename)) {
                $query = "SELECT currval('" . $tablename[1] . "_seq') AS last_value";
                $result = pg_query($this->dbLink, $query);

                if (!$result) {
                    return false;
                }

                $temp_result = pg_fetch_assoc($result, NULL);
                pg_free_result($result);

                return ($temp_result) ? $temp_result['last_value'] : false;
            }
        }

        return false;
    }

    /**
     * 开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        if($this->Transactions == false){
            if ($this->query('BEGIN')) {
                $this->Transactions = true;
            }
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
            if ($this->query('COMMIT')) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL Commit failed! <br/>Error Message:' . $this->error());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL Commit failed. Error Message:' . $this->error());
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
            if ($this->query('ROLLBACK')) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = doit::getControllerName();
                $actionId            = doit::getActionName();

                if (DOIT_DEBUG === true) {
                    Controller::halt('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed! <br/>Error Message:' . $this->error());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed. Error Message:' . $this->error());
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

        return pg_escape_string($string);
    }

    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        if($this->dbLink == true) {
            @pg_close($this->dbLink);
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