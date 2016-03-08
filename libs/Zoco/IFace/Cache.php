<?php

namespace Zoco\IFace;

/**
 * Interface Cache
 *
 * @package Zoco\IFace
 */
interface Cache {
    /**
     * 设置缓存
     *
     * @param     $key
     * @param     $value
     * @param int $expire
     * @return mixed
     */
    public function set($key, $value, $expire = 0);

    /**
     * 获取缓存值
     *
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * 删除缓存值
     *
     * @param $key
     * @return mixed
     */
    public function delete($key);
}