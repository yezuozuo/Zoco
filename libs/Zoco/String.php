<?php

namespace Zoco;

/**
 * Class String
 *
 * @package Zoco
 */
class String {
    /**
     * @var string
     */
    protected $string;

    /**
     * @param $string
     */
    public function __construct($string) {
        $this->string = $string;
    }

    /**
     * 比较两个版本
     *
     * @param $version1
     * @param $version2
     * @return int
     * @throws \Exception
     */
    static public function versionCompare($version1, $version2) {
        if (!Validate::isVersion($version1) || !Validate::isVersion($version2)) {
            throw new \Exception("[$version1] or [$version2] is not a version string.");
        }

        $v1 = explode('.', $version1);
        $v2 = explode('.', $version2);

        $c1 = count($v1);
        $c2 = count($v2);

        $count = $c1 > $c2 ? $c2 : $c1;
        for ($i = 0; $i < $count; $i++) {
            $_v1 = intval($v1[$i]);
            $_v2 = intval($v2[$i]);

            if ($_v1 > $_v2) {
                return 1;
            } else {
                if ($_v1 < $_v2) {
                    return -1;
                } else {
                    continue;
                }
            }
        }

        return 0;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->string;
    }

    /**
     * @param $findStr
     * @return bool|int
     */
    public function pos($findStr) {
        return strpos($this->string, $findStr);
    }

    /**
     * 从右向坐找
     *
     * @param $findStr
     * @return bool|int
     */
    public function rpos($findStr) {
        return strrpos($this->string, $findStr);
    }

    /**
     * @param $findStr
     * @return int
     */
    public function ripos($findStr) {
        return strripos($this->string, $findStr);
    }

    /**
     * @param $findStr
     * @return bool|int
     */
    public function ipos($findStr) {
        return stripos($this->string, $findStr);
    }

    /**
     * @return String
     */
    public function lower() {
        return new String(strtolower($this->string));
    }

    /**
     * @return String
     */
    public function upper() {
        return new String(strtoupper($this->string));
    }

    /**
     * @return int
     */
    public function len() {
        return strlen($this->string);
    }

    /**
     * @param      $offset
     * @param null $length
     * @return String
     */
    public function substr($offset, $length = null) {
        return new String(substr($this->string, $offset, $length));
    }

    /**
     * @param      $search
     * @param      $replace
     * @param null $count
     * @return String
     */
    public function replace($search, $replace, &$count = null) {
        return new String(str_replace($search, $replace, $this->string, $count));
    }

    /**
     * @param $needle
     * @return bool
     */
    public function startWith($needle) {
        return strpos($this->string, $needle) === 0;
    }

    /**
     * @param $needle
     * @return bool
     */
    public function endWith($needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($this->string, -$length) === $needle);
    }

    /**
     * @param      $sp
     * @param null $limit
     * @return \ArrayObject
     */
    public function split($sp, $limit = null) {
        if ($limit === null) {
            return explode($sp, $this->string);
        } else {
            return explode($sp, $this->string, $limit);
        }
    }

    /**
     * @param int $splitLength
     * @return \ArrayObject
     */
    public function toArray($splitLength = 1) {
        return new \ArrayObject(str_split($this->string, $splitLength));
    }
}