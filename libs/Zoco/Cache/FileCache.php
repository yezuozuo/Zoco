<?php

namespace Zoco\Cache;

/**
 * 文件缓存类，提供类似memcache的接口
 * 警告：此类仅用于测试，不作为生产环境的代码，请使用Key-Value缓存系列！
 * Class FileCache
 *
 * @package Zoco\Cache
 */
class FileCache implements \Zoco\IFace\Cache {
    /**
     * @var
     */
    protected $config;

    /**
     * @param $config
     * @throws \Exception
     */
    public function __construct($config) {
        if (!isset($config['cacheDir'])) {
            throw new \Exception(__CLASS__ . ":require cache_dir");
        }
        if (!is_dir($config['cacheDir'])) {
            mkdir($config['cacheDir'], 0755, true);
        }
        $this->config = $config;
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $timeout
     * @return int
     */
    public function set($key, $value, $timeout = 0) {
        $file            = $this->getFileName($key);
        $data['value']   = $value;
        $data['timeout'] = $timeout;
        $data['mktime']  = time();

        return file_put_contents($file, serialize($data));
    }

    /**
     * @param $key
     * @return string
     */
    protected function getFileName($key) {
        $file = $this->config['cacheDir'] . '/' . trim(str_replace('_', '/', $key), '/');
        $dir  = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $file;
    }

    /**
     * @param $key
     * @return bool
     */
    public function get($key) {
        $file = $this->getFileName($key);
        if (!is_file($file)) {
            return false;
        }
        $data = unserialize(file_get_contents($file));

        if (empty($data) || !isset($data['timeout']) || !isset($data['value'])) {
            return false;
        }

        /**
         * 已过期
         */
        if ($data['timeout'] != 0 & ($data['mktime'] + $data['timeout']) < time()) {
            $this->delete($key);

            return false;
        }

        return $data['value'];
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key) {
        $file = $this->getFileName($key);

        return unlink($file);
    }
}