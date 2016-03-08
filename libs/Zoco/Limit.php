<?php

namespace Zoco;

/**
 * 使用了redis的频率限制组件
 * Class Limit
 *
 * @package Zoco
 */
class Limit {
    const PREFIX = 'zoco:limit:';

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * 构造防范，需要传入一个redisId
     *
     * @param $config
     */
    public function __construct($config) {
        if (empty($config['redisId'])) {
            $config['redisId'] = 'master';
        }
        $this->redis = \Zoco::$php->redis($config['redisId']);
    }

    /**
     * 增加计数
     *
     * @param     $key
     * @param int $expire
     * @param int $incrby
     * @return bool|int
     */
    public function addCount($key, $expire = 86400, $incrby = 1) {
        $key = self::PREFIX . $key;

        /**
         * 增加计数
         */
        if ($this->redis->exists($key)) {
            return $this->redis->incr($key);
        } /**
         * 不存在的key，设置为1
         */
        else {
            return $this->redis->set($key, $incrby, $expire);
        }
    }

    /**
     * 检查是否超过了频率限制，如果超过返回false，未超过返回true
     *
     * @param $key
     * @param $limit
     * @return bool
     */
    public function exceed($key, $limit) {
        $key   = self::PREFIX . $key;
        $count = $this->redis->get($key);
        if (!empty($count) && $count > $limit) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 清除频率
     *
     * @param $key
     * @return int
     */
    public function reset($key) {
        $key = self::PREFIX . $key;

        return $this->redis->del($key);
    }
}