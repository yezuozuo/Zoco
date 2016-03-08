<?php

namespace Zoco;

/**
 * 所有Zoco应用类的基类
 * Class Object
 *
 * @package Zoco
 * @property Database     $db
 * @property IFace\Cache  $cache
 * @property Upload       $upload
 * @property Session      $session
 * @property Template     $tpl
 * @property \Redis       $redis
 * @property \MongoClient $mongo
 * @property Config       $config
 * @property Log          $log
 * @property Auth         $user
 * @property URL          $url
 * @property Limit        $limit
 * @method   Database     db($database = null)
 * @method   \MongoClient mongo
 * @method   \Redis       redis
 * @method   IFace\Cache  cache
 * @method   URL          url
 */
class Object {
    /**
     * @var
     */
    public $zoco;

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key) {
        return $this->zoco->$key;
    }

    /**
     * @param $func
     * @param $param
     * @return mixed
     */
    public function __call($func, $param) {
        return call_user_func_array(array($this->zoco, $func), $param);
    }
}