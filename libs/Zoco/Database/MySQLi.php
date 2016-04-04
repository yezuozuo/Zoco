<?php

namespace Zoco\Database;

/**
 * MySQL数据库封装类
 * Class MySQLi
 *
 * @package Zoco\Database
 */
class MySQLi extends \mysqli implements \Zoco\IDatabase {
    const DEFAULT_PORT = 3306;

    /**
     * @var bool
     */
    public $debug = false;

    /**
     * @var null
     */
    public $conn = null;

    /**
     * @var string
     */
    public $config;

    /**
     * @var bool
     */
    public $displayError = true;

    /**
     * @param string $dbConfig
     */
    public function __construct($dbConfig) {
        if (empty($dbConfig['port'])) {
            $dbConfig['port'] = self::DEFAULT_PORT;
        }
        $this->config = $dbConfig;
    }

    /**
     * @return mixed
     */
    public function lastInsertId() {
        return $this->insert_id;
    }

    /**
     * 执行一个SQL语句
     *
     * @param string $sql
     * @return bool|MySQLRecord
     */
    public function query($sql) {
        $result = false;
        for ($i = 0; $i < 2; $i++) {
            $result = parent::query($sql);
            if ($result == false) {
                if ($this->errno == 2013 || $this->errno == 2006) {
                    $r = $this->checkConnection();
                    if ($r === true) {
                        continue;
                    }
                } else {
                    if ($this->displayError) {
                        trigger_error(__CLASS__ . " SQL Error: " . $this->errorMessage($sql), E_USER_WARNING);
                        echo \Zoco\Error::info("SQL Error", $this->errorMessage($sql));
                    }

                    return false;
                }
            }
            break;
        }
        if (!$result) {
            echo \Zoco\Error::info("SQL Error", $this->errorMessage($sql));

            return false;
        }

        return new MySQLiRecord($result);
    }

    /**
     * 检查数据库连接,是否有效，无效则重新建立
     *
     * @return bool|mixed|void
     */
    public function checkConnection() {
        if (!@$this->ping()) {
            $this->close();

            return $this->connect();
        }

        return true;
    }

    /**
     * @param null $host
     * @param null $user
     * @param null $password
     * @param null $database
     * @param null $port
     * @param null $socket
     * @return bool
     */
    public function connect(
        $host = null,
        $user = null,
        $password = null,
        $database = null,
        $port = null,
        $socket = null
    ) {
        $dbConfig =& $this->config;
        if (!empty($dbConfig['persistent'])) {
            $dbConfig['host'] = 'p:' . $dbConfig['host'];
        }
        parent::connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['name'], $dbConfig['port']);

        if (mysqli_connect_errno()) {
            trigger_error("Mysqli connect failed: " . mysqli_connect_error());

            return false;
        }

        if (!empty($dbConfig['charset'])) {
            $this->set_charset($dbConfig['charset']);
        }

        return true;
    }

    /**
     * SQL错误信息
     *
     * @param $sql
     * @return string
     */
    protected function errorMessage($sql) {
        $msg = $this->error . "<hr />$sql<hr />";
        $msg .= "Server: {$this->config['host']}:{$this->config['port']}.<br/>";
        $msg .= "Message: {$this->error}<br/>";
        $msg .= "Errno: {$this->errno}";

        return $msg;
    }

    /**
     * 过滤特殊字符
     *
     * @param $value
     * @return string
     */
    public function quote($value) {
        return $this->escape_string($value);
    }

    /**
     * 获取错误码
     *
     * @return int
     */
    public function errno() {
        return $this->errno;
    }

    /**
     * 返回上一个insert语句的自增主键ID
     *
     * @return mixed
     */
    public function insertId() {
        return $this->insert_id;
    }

    public function update() {
    }

    public function insert() {
    }

    public function delete() {
    }
}

/**
 * Class MySQLiRecord
 *
 * @package Zoco\Database
 */
class MySQLiRecord implements \Zoco\IDbRecord {
    /**
     * @var \mysqli_result
     */
    public $result;

    /**
     * @param $result
     */
    public function __construct($result) {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function fetch() {
        return $this->result->fetch_assoc();
    }

    /**
     * @return array
     */
    public function fetchAll() {
        $data = array();
        while ($record = $this->result->fetch_assoc()) {
            $data[] = $record;
        }

        return $data;
    }
}