<?php

namespace Zoco\Log;

/**
 * 数据库日志记录类
 * Class DBLog
 *
 * @package Zoco\Log
 */
class DBLog extends \Zoco\Log implements \Zoco\IFace\Log {
    /**
     * @var \Zoco\Database
     */
    protected $db;

    /**
     * @var
     */
    protected $table;

    /**
     * @param $params
     */
    public function __init($params) {
        $this->table = $params['table'];
        $this->db    = $params['db'];
    }

    /**
     * @param          $msg
     * @param int|null $level
     */
    public function put($msg, $level = self::INFO) {
        $put['logType'] = self::convert($level);
        $msg            = $this->format($msg, $level);
        if ($msg) {
            $put['msg'] = $msg;
            \Zoco::$php->db->insert($put, $this->table);
        }
    }

    /**
     * @return mixed
     */
    public function create() {
        return $this->db->query(
            "CREATE TABLE `{$this->table}` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`addtime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			`logtype` TINYINT NOT NULL ,
			`msg` VARCHAR(255) NOT NULL)"
        );
    }
}