<?php

namespace Zoco\Database;

/**
 * MySQL数据库封装类
 * Class MySQL
 *
 * @package Zoco\Database
 */
class MySQL implements \Zoco\IDatabase {
    /**
     * 数据库默认端口
     */
    const DEFAULT_PORT = 3306;
    /**
     * 调试开关
     *
     * @var bool
     */
    public $debug = false;
    /**
     * 连接句柄
     *
     * @var
     */
    public $conn;
    /**
     * 配置文件
     *
     * @var
     */
    public $config;

    /**
     * @param $dbConfig
     */
    public function __construct($dbConfig) {
        if (empty($dbConfig['port'])) {
            $dbConfig['port'] = self::DEFAULT_PORT;
        }
        $this->config = $dbConfig;
    }

    /**
     * 执行一个SQL语句
     *
     * @param $sql
     * @return bool|MySQLRecord
     */
    public function query($sql) {
        $res = false;
        for ($i = 0; $i < 2; $i++) {
            $res = mysql_query($sql, $this->conn);
            if ($res === false) {
                if (mysql_errno($this->conn) == 2006 || mysql_errno($this->conn) == 2013) {
                    $r = $this->checkConnection();
                    if ($r === true) {
                        continue;
                    }
                }
                echo \Zoco\Error::info("SQL Error", $this->errorMessage($sql));

                return false;
            }
            break;
        }
        if (!$res) {
            echo \Zoco\Error::info("SQL Error", $this->errorMessage($sql));

            return false;
        }

        return new MySQLRecord($res);
    }

    /**
     * 检查数据库连接,是否有效，无效则重新建立
     *
     * @return bool
     */
    protected function checkConnection() {
        if (!@$this->ping()) {
            $this->close();
            $this->connect();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function ping() {
        if (!mysql_ping($this->conn)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 关闭连接
     */
    public function close() {
        mysql_close($this->conn);
    }

    /**
     * 连接数据库
     */
    public function connect() {
        $dbConfig = $this->config;
        if (empty($dbConfig['persistent'])) {
            $this->conn = mysql_connect($dbConfig['host'] . ':' . $dbConfig['port'], $dbConfig['user'], $dbConfig['password']);
        } /**
         * 长连接
         */
        else {
            $this->conn = mysql_pconnect($dbConfig['host'] . ':' . $dbConfig['port'], $dbConfig['user'], $dbConfig['password']);
        }

        if (!$this->conn) {
            echo \Zoco\Error::info("SQL Error", mysql_error($this->conn));
            exit();
        }
        mysql_select_db($dbConfig['name'], $this->conn) or \Zoco\Error::info("SQL Error", mysql_error($this->conn));
    }

    /**
     * @param $sql
     * @return string
     */
    public function errorMessage($sql) {
        return mysql_error($this->conn) . "<hr/>$sql<hr/>MySQL Server:{$this->config['host']}:{$this->config['port']}";
    }

    /**
     * 返回上一个insert语句的自增主键ID
     */
    public function lastInsertId() {
        echo mysql_insert_id($this->conn);
    }

    /**
     * 获取上一次操作影响的行数
     *
     * @return int
     */
    public function affectedRows() {
        return mysql_affected_rows($this->conn);
    }

    public function insert() {
    }

    public function update() {
    }

    public function delete() {
    }
}

/**
 * Class MySQLRecord
 *
 * @package Zoco\Database
 */
class MySQLRecord implements \Zoco\IDbRecord {
    /**
     * @var
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
        return mysql_fetch_assoc($this->result);
    }

    /**
     * @return array
     */
    public function fetchAll() {
        $data = array();
        while ($record = mysql_fetch_assoc($this->result)) {
            $data[] = $record;
        }

        return $data;
    }

    public function free() {
        mysql_free_result($this->result);
    }
}