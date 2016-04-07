<?php

namespace Zoco;

/**
 * Database Driver接口
 * 数据库驱动类的接口
 * Interface IDatabase
 *
 * @package Zoco
 */
interface IDatabase {
    /**
     * 执行
     *
     * @param $sql
     * @return mixed
     */
    public function query($sql);

    /**
     * 连接
     *
     * @return mixed
     */
    public function connect();

    /**
     * 关闭
     *
     * @return mixed
     */
    public function close();

    /**
     * 上次插入的ID
     *
     * @return mixed
     */
    public function lastInsertId();

    /**
     * ping
     *
     * @return mixed
     */
    public function ping();

    /**
     * 插入
     *
     * @return mixed
     */
    public function insert();

    /**
     * 更新
     *
     * @return mixed
     */
    public function update();

    /**
     * 删除
     *
     * @return mixed
     */
    public function delete();
}

/**
 * Database Driver接口
 * 数据库结果集的接口，提供两种接口
 * Interface IDbRecord
 *
 * @package Zoco
 */
interface IDbRecord {
    /**
     * 获取单条数据
     *
     * @return mixed
     */
    public function fetch();

    /**
     * 获取全部数据到数组
     *
     * @return mixed fetchAll
     */
    public function fetchAll();
}

/**
 * Database类，处理数据库连接和基本的SQL组合
 * 提供4种接口，query、insert、update、delete
 * Class Database
 *
 * @package Zoco
 * @method  lastInsertId()
 * @method  connect（）
 */
class Database {
    const TYPE_MYSQL  = 1;
    const TYPE_MYSQLi = 2;
    const TYPE_PDO    = 3;
    /**
     * @var bool debug
     */
    public $debug = false;
    /**
     * 读取次数
     *
     * @var int
     */
    public $readTimes = 0;
    /**
     * 写入次数
     *
     * @var int
     */
    public $writeTimes = 0;
    /**
     * 数据库
     *
     * @var null|Database\PdoDB
     */
    public $_db = null;
    /**
     * @var null|SelectDB
     */
    public $dbApt = null;

    /**
     * @param $dbConfig
     */
    public function __construct($dbConfig) {
        switch ($dbConfig['type']) {
            case self::TYPE_MYSQL:
                $this->_db = new Database\MySQL($dbConfig);
                break;
            case self::TYPE_MYSQLi:
                $this->_db = new Database\MySQLi($dbConfig);
                break;
            default:
                $this->_db = new Database\PDODB($dbConfig);
                break;
        }
        $this->dbApt = new SelectDB($this);
    }

    /**
     * 初始化
     */
    public function __init() {
        $this->checkStatus();
        $this->dbApt->__init();
        $this->readTimes  = 0;
        $this->writeTimes = 0;
    }

    /**
     * 检查连接状态
     * 如果连接断开，则重新连接
     */
    public function checkStatus() {
        if (!$this->_db->ping()) {
            $this->_db->close();
            $this->_db->connect();
        }
    }

    /**
     * 启动事务处理
     *
     * @return bool|mixed|\PDOStatement
     */
    public function start() {
        return $this->query('START TRANSACTION');
    }

    /**
     * 执行一条SQL语句
     *
     * @param $sql
     * @return bool|mixed|\PDOStatement
     */
    public function query($sql) {
        if ($this->debug) {
            echo "$sql<br\>\n<hr\>";
        }
        $this->readTimes++;

        return $this->_db->query($sql);
    }

    /**
     * 提交事务处理
     *
     * @return bool|mixed|\PDOStatement
     */
    public function commit() {
        return $this->query('COMMIT');
    }

    /**
     * 事务回滚
     *
     * @return bool|mixed|\PDOStatement
     */
    public function rollback() {
        return $this->query('ROLLBACK');
    }

    /**
     * 向数据库的表$table插入数据$data
     * $data为键值对应的
     *
     * @param $data
     * @param $table
     * @return mixed
     */
    public function insert($data, $table) {
        $this->dbApt->__init();
        $this->dbApt->from($table);
        $this->writeTimes++;

        return $this->dbApt->insert($data);
    }

    /**
     * 从$table删除一条$where为$id的记录
     *
     * @param        $id
     * @param        $table
     * @param string $where
     * @return bool|mixed|\PDOStatement
     */
    public function delete($id, $table, $where = 'id') {
        if (func_num_args() < 2) {
            echo Error::info('SelectDB param error', 'Delete must have 2 params ($id,$table)!');

            return false;
        }
        $this->dbApt->__init();
        $this->dbApt->from($table);
        $this->writeTimes++;

        return $this->query("delete from $table where $where='$id'");
    }

    /**
     * 执行数据库更新操作，参数为主键ID，值$data，必须是键值对应的
     *
     * @param        $id
     * @param        $data
     * @param        $table
     * @param string $where
     * @return bool
     */
    public function update($id, $data, $table, $where = 'id') {
        if (func_num_args() < 3) {
            echo Error::info('SelectDB param error', 'Update must have 3 params ($id,$data,$table)!');

            return false;
        }
        $this->dbApt->__init();
        $this->dbApt->from($table);
        $this->dbApt->where("$where='$id'");
        $this->writeTimes++;

        return $this->dbApt->update($data);
    }

    /**
     * 根据主键获取单条数据
     *
     * @param        $id
     * @param        $table
     * @param string $primary
     * @return mixed
     */
    public function get($id, $table, $primary = 'id') {
        $this->dbApt->__init();
        $this->dbApt->from($table);
        $this->dbApt->where("$primary='$id'");

        return $this->dbApt->getOne();
    }

    /**
     * 调用$driver的自带方法
     *
     * @param       $method
     * @param array $args
     */
    public function __call($method, $args = array()) {
        call_user_func_array(array($this->_db, $method), $args);
    }
}