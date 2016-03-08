<?php

namespace Zoco;

/**
 * 页面缓存类
 * Class PageCache
 *
 * @package Zoco
 */
class PageCache {
    /**
     * 目录
     *
     * @var string
     */
    public $cacheDir;

    /**
     * 过期时间
     *
     * @var int
     */
    public $expire;

    /**
     * @param int    $expire
     * @param string $cacheDir
     */
    public function __construct($expire = 3600, $cacheDir = '') {
        $this->expire = $expire;
        if ($cacheDir === '') {
            $this->cacheDir = WEBPATH . '/data/cache/pageCache';
        } else {
            $this->cacheDir = $cacheDir;
        }
    }

    /**
     * 建立缓存类
     *
     * @param $content
     */
    public function create($content) {
        file_put_contents($this->cacheDir . '/' . base64_encode($_SERVER['REQUEST_URI']) . '.html', $content);
    }

    /**
     * 加载缓存
     */
    public function load() {
        include($this->cacheDir . '/' . base64_encode($_SERVER['REQUEST_URI']) . '.html');
    }

    /**
     * 检测是否存在有效缓存
     *
     * @return bool
     */
    public function isCached() {
        $file = $this->cacheDir . '/' . base64_encode($_SERVER['REQUEST_URI']) . '.html';
        if (!file_exists($file)) {
            return false;
        } else {
            if (fileatime($file) + $this->expire < time()) {
                return false;
            } else {
                return true;
            }
        }
    }
}