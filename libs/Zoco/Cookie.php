<?php

namespace Zoco;

/**
 * Class Cookie
 *
 * @package Zoco
 */
class Cookie {
    /**
     * 路径
     *
     * @var string
     */
    public static $path = '/';

    /**
     * 主机
     *
     * @var null
     */
    public static $domain = null;

    /**
     * 安全
     *
     * @var bool
     */
    public static $secure = false;

    /**
     * 是否只有http
     *
     * @var bool
     */
    public static $httponly = false;

    /**
     * @param      $key
     * @param null $default
     * @return null
     */
    static public function get($key, $default = null) {
        if (!isset($_COOKIE[$key])) {
            return $default;
        } else {
            return $_COOKIE[$key];
        }
    }

    /**
     * @param $key
     */
    static public function delete($key) {
        unset($_COOKIE[$key]);
        self::set($key, '');
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $expire
     */
    static public function set($key, $value, $expire = 0) {
        if ($expire != 0) {
            $expire = time() + $expire;
        }
        setcookie($key, $value, $expire);
    }
}