<?php

namespace Zoco\Queue;

class Redis implements \Zoco\IFace\Queue {
    /**
     * @var \Redis
     */
    public $_redis;
    /**
     * @var
     */
    public $config;

    /**
     * @var string
     */
    static public $prefix = 'zoco_queue';

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
     * @param int $timeout
     * @return bool|string
     */
    public function pop() {
        return $this->_redis->rPop(self::$prefix);
    }

    /**
     * @param $value
     * @return int
     */
    public function push($value) {
        return $this->_redis->lPush(self::$prefix, $value);
    }

    /**
     * @param $limit
     * @return array
     */
    public function getMulti($limit) {
        $ret = array();
        for ($i = 0; $i < $limit; $i++) {
            $item = $this->pop();
            if (false === $item) {
                break;
            }
            $ret[] = $item;
        }

        return $ret;
    }
}