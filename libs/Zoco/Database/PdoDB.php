<?php

namespace Zoco\Database;

/**
 * Class PdoDB
 *
 * @package Zoco\Database
 */
class PdoDB extends \PDO implements \Zoco\IDatabase {
    /**
     * @var bool
     */
    public $debug = false;

    /**
     * @var
     */
    protected $config;

    /**
     * @param $dbConfig
     */
    public function __construct($dbConfig) {
        $this->config = $dbConfig;
    }

    public function connect() {
        $dbConfig =& $this->config;
        $dsn      = $dbConfig['dbms'] . ":host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['name'];

        if (!empty($dbConfig['persistent'])) {
            parent::__construct($dsn, $dbConfig['user'], $dbConfig['password'], array(\PDO::ATTR_PERSISTENT => true));
        } else {
            parent::__construct($dsn, $dbConfig['user'], $dbConfig['password']);
        }

        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * @param string $str
     * @param null   $paramType
     * @return string
     */
    public function quote($str, $paramType = null) {
        return trim(parent::quote($str, $paramType), '\'');
    }

    /**
     * 执行一个SQL语句
     *
     * @param string $sql
     * @return bool
     */
    public final function query($sql) {
        if ($this->debug) {
            echo "$sql<br/>\n<hr/>";
        }
        $res = parent::query($sql) || \Zoco\Error::info("SQL ERROR", implode(',', $this->errorInfo()) . "<hr/>$sql");

        return $res;
    }

    /**
     * 执行一个参数化SQL语句,并返回一行结果
     *
     * @param $sql
     * @return bool|mixed
     */
    public final function queryLine($sql) {
        $params = func_get_args();
        if ($this->debug) {
            var_dump($params);
        }
        array_shift($params);
        $stm = $this->prepare($sql);
        if ($stm->execute($params)) {
            $ret = $stm->fetch();
            $stm->closeCursor();

            return $ret;
        } else {
            \Zoco\Error::info("SQL Error", implode(", ", $this->errorInfo()) . "<hr />$sql");

            return false;
        }
    }

    /**
     * 执行一个参数化SQL语句,并返回所有结果
     *
     * @param $sql
     * @return array|bool
     */
    public final function queryAll($sql) {
        $params = func_get_args();
        if ($this->debug) {
            var_dump($params);
        }
        array_shift($params);
        $stm = $this->prepare($sql);
        if ($stm->execute($params)) {
            $ret = $stm->fetchAll();
            $stm->closeCursor();

            return $ret;
        } else {
            \Zoco\Error::info("SQL Error", implode(", ", $this->errorInfo()) . "<hr />$sql");

            return false;
        }
    }

    /**
     * 执行一个参数化SQL语句
     *
     * @param $sql
     * @return bool|string
     */
    public final function execute($sql) {
        $params = func_get_args();
        if ($this->debug) {
            var_dump($params);
        }
        array_shift($params);
        $stm = $this->prepare($sql);
        if ($stm->execute($params)) {
            return $this->lastInsertId();
        } else {
            \Zoco\Error::info("SQL Error", implode(", ", $this->errorInfo()) . "<hr />$sql");

            return false;
        }
    }

    /**
     * 关闭连接，释放资源
     */
    public function close() {
        unset($this);
    }

    public function update() {
    }

    public function delete() {
    }

    public function ping() {
    }

    public function insert() {
    }
}
