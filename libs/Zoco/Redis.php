<?php

namespace Zoco;

/**
 * Class Redis
 *
 * @package Zoco
 */
class Redis {

    /**
     * 在redis中的前缀
     *
     * @var string
     */
    static public $prefix = 'zoco_auto_key_';
    /**
     * @var \Redis
     */
    public $_redis;
    /**
     * @var
     */
    public $config;

    /**
     * @param $config
     */
    public function __construct($config) {
        $this->_redis = new \Redis();
        $this->config = $config;
        $this->connect();

        if (!empty($this->config['password'])) {
            $this->_redis->auth($this->config['password']);
        }
        if (!empty($this->config['database'])) {
            $this->_redis->select($this->config['database']);
        }
    }

    /**
     * @return bool
     */
    public function connect() {
        try {
            if ($this->config['pconnect']) {
                return $this->_redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
            } else {
                return $this->_redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }
        } catch (\RedisException $e) {
            echo \Zoco\Error::info('redis connect error', __CLASS__ . "Redis Exception" . var_export($e, 1));

            return false;
        }
    }

    /**
     * @param     $appKey
     * @param int $initId
     * @return bool|int
     */
    static public function getIncreaseId($appKey, $initId = 1000) {
        if (empty($appKey)) {
            return false;
        }
        $mainKey = self::$prefix . $appKey;

        if (\Zoco::$php->redis->exists($mainKey)) {
            $inc = \Zoco::$php->redis->incr($mainKey);
            if (empty($inc)) {
                \Zoco::$php->log->put("redis::incr() failed. Error: " . \Zoco::$php->redis->getLastError());

                return false;
            }

            return $inc;
        } else {
            if (\Zoco::$php->redis->getLastError()) {
                return false;
            } else {
                $init = \Zoco::$php->redis->set($mainKey, $initId);
                if ($init == false) {
                    \Zoco::$php->log->put("redis::set() failed. Error: " . \Zoco::$php->redis->getLastError());

                    return false;
                } else {
                    return $initId;
                }
            }
        }
    }

    /**
     * @param       $method
     * @param array $args
     * @return bool|int|mixed
     */
    public function __call($method, $args = array()) {
        $result = false;
        for ($i = 0; $i < 2; $i++) {
            try {
                $result = call_user_func_array(array($this->_redis, $method), $args);
            } catch (\RedisException $e) {
                echo \Zoco\Error::info('redis connect error', __CLASS__ . "Redis Exception" . var_export($e, 1));
                $result = -1;
            }

            /**
             * 异常重试一次
             */
            if ($result === -1) {
                $r = $this->checkConnection();
                if ($r === true) {
                    continue;
                }
            }
            break;
        }
        if ($result === -1) {
            return false;
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function checkConnection() {
        if (!@$this->_redis->ping()) {
            $this->_redis->close();

            return $this->connect();
        }

        return true;
    }


}