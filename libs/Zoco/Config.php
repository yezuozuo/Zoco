<?php

namespace Zoco;

/**
 * Class Config
 *
 * @package Zoco
 */
class Config extends \ArrayObject {
    /**
     * 显示调试信息
     *
     * @var bool
     */
    static $debug = false;
    /**
     * 是否激活
     *
     * @var bool
     */
    static $active = false;
    /**
     * 配置文件的路径
     *
     * @var array
     */
    protected $configPath;

    /**
     * @param $dir
     */
    public function setPath($dir) {
        $this->configPath[] = $dir;
        self::$active       = true;
    }

    /**
     * @param mixed $index
     * @return bool
     */
    public function offsetGet($index) {
        if (!isset($this->config[$index])) {
            $this->load($index);
        }

        return isset($this->config[$index]) ? $this->config[$index] : false;
    }

    /**
     * @param $index
     */
    public function load($index) {
        foreach ($this->configPath as $path) {
            $fileName = $path . '/' . $index . '.php';
            if (is_file($fileName)) {
                $retData = include $fileName;
                if (empty($retData) && self::$debug) {
                    trigger_error(__CLASS__ . ": $fileName no return data");
                } else {
                    $this->config[$index] = $retData;
                }
            } else {
                if (self::$debug) {
                    trigger_error(__CLASS__ . ": fileName no exists");
                }
            }
        }
    }

    /**
     * @param mixed $index
     * @param mixed $newVal
     */
    public function offsetSet($index, $newVal) {
        $this->config[$index] = $newVal;
    }

    /**
     * @param mixed $index
     */
    public function offsetUnset($index) {
        unset($this->config[$index]);
    }

    /**
     * @param mixed $index
     * @return bool
     */
    public function offsetExists($index) {
        return isset($this->config[$index]);
    }
}