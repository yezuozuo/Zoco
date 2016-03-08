<?php

namespace Zoco\Cache;

/**
 * 使用Redis作为缓存
 * Class Redis
 *
 * @package Zoco\Cache
 */
class Redis implements \Zoco\IFace\Cache {
    /**
     * @var
     */
    protected $config;

    /**
     * @var
     */
    protected $redis;

    /**
     * @param $config
     */
    public function __construct($config) {
        if (empty($config['redisId'])) {
            $config['redisId'] = 'master';
        }
        $this->config = $config;
        $this->redis  = \Zoco::$php->redis($config['redisId']);
    }

    /**
     * 设置缓存
     *
     * @param     $key
     * @param     $value
     * @param int $expire
     * @return mixed
     */
    public function set($key, $value, $expire = 0) {
        return $this->redis->setex($key, $expire, $value);
    }

    /**
     * 获取缓存
     *
     * @param $key
     * @return mixed
     */
    public function get($key) {
        return $this->redis->get($key);
    }

    /**
     * 删除缓存
     *
     * @param $key
     * @return mixed
     */
    public function delete($key) {
        return $this->redis->del($key);
    }

    /**
     * @param $key
     * @return int
     */
    public function incr($key) {
        return $this->redis->incr($key);
    }
}