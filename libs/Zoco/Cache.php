<?php

namespace Zoco;

/**
 * 缓存制造类，缓存基类
 * Class Cache
 *
 * @package Zoco
 * @method get($key)
 * @method set($key, $value, $expire = 0)
 * @method delete($key)
 * @method incr($key)
 * @method setTable($table)
 * @method sets($key)
 * @method setm($key)
 * @method save()
 */
class Cache {
    const TYPE_FILE  = 0;
    const TYPE_DB    = 1;
    const TYPE_REDIS = 2;
    /**
     * @var array
     */
    static $backend = array(
        'FileCache',
        'DBCache',
        'RedisCache',
    );
    /**
     * @var
     */
    public $cache;

    /**
     * 获取缓存对象
     *
     * @param $config
     * @return mixed
     */
    public function create($config) {
        if (empty(self::$backend[$config['type']])) {
            echo Error::info('Cache Error', "cache backend: {$config['type']} no support");
            exit();
        }
        $backend = "\\Zoco\\Cache\\" . self::$backend[$config['type']];

        return new $backend($config);
    }
}