<?php

namespace Zoco;

/**
 * 过滤类
 * 用于过滤外部输入得到数据，过滤数组或者变量中的不安全的字符，以及HTML标签
 * Class Filter
 *
 * @package Zoco
 */
class Filter {
    /**
     * @var bool
     */
    static $errorUrl;

    /**
     * @var
     */
    static $originGet;

    /**
     * @var
     */
    static $originPost;

    /**
     * @var
     */
    static $originCookie;

    /**
     * @var
     */
    static $originRequest;

    /**
     * @var string
     */
    public $mode;

    /**
     * @param string     $mode
     * @param bool|false $errorUrl
     */
    public function __construct($mode = 'deny', $errorUrl = false) {
        $this->mode     = $mode;
        self::$errorUrl = $errorUrl;
    }

    /**
     * 过滤$_GET $_POST $_REQUEST $_COOKIE
     */
    static public function request() {
        self::$originGet     = $_GET;
        self::$originPost    = $_POST;
        self::$originRequest = $_REQUEST;
        self::$originCookie  = $_COOKIE;

        $_POST    = Filter::filterArray($_POST);
        $_GET     = Filter::filterArray($_GET);
        $_REQUEST = Filter::filterArray($_REQUEST);
        $_COOKIE  = Filter::filterArray($_COOKIE);
    }

    /**
     * 过滤数组
     *
     * @param $array
     * @return array|bool
     */
    static public function filterArray($array) {
        if (!is_array($array)) {
            return false;
        }

        $clean = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::filterArray($value);
            } else {
                $value = self::escape($value);
                $key   = self::escape($key);
            }
            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * 使输入的代码安全
     *
     * @param $string
     * @return string
     */
    static public function escape($string) {
        if (is_numeric($string)) {
            return $string;
        }
        $string = htmlspecialchars($string);
        $string = self::addslash($string);

        return $string;
    }

    /**
     * 过滤危险字符
     *
     * @param $string
     * @return string
     */
    static public function addslash($string) {
        return addslashes($string);
    }

    /**
     * @param $content
     */
    static public function safe(&$content) {
        $content = stripcslashes($content);
        $content = html_entity_decode($content, ENT_QUOTES, \Zoco::$charset);
    }

    /**
     * @param $var
     * @param $type
     * @return bool|float|int|string
     */
    static public function filterVar($var, $type) {
        switch ($type) {
            case 'int':
                return intval($var);
            case 'string':
                return htmlspecialchars(strval($var), ENT_QUOTES);
            case 'float':
                return floatval($var);
            default:
                return false;
        }
    }

    /**
     * 移除反斜杠过滤
     *
     * @param $string
     * @return string
     */
    static public function deslash($string) {
        return stripcslashes($string);
    }

    /**
     * @param $param
     */
    public function post($param) {
        $this->_check($_POST, $param);
    }

    /**
     * 根据提供的参数对数据进行检查
     *
     * @param $data
     * @param $param
     */
    public function _check(&$data, $param) {
        foreach ($data as $key => $value) {
            if (!isset($data[$key])) {
                if (isset($value['require'])) {
                    self::raise('param require');
                } else {
                    continue;
                }
            }

            if (isset($value['type'])) {
                $data[$key] = Validate::$value['type']($data[$key]);
                if ($data[$key] === false) {
                    self::raise();
                }

                /**
                 * 最小值参数
                 */
                if (isset($value['min']) && is_numeric($data[$key]) && $data[$key] < $value['min']) {
                    self::raise('num too small');
                }

                /**
                 * 最大值参数
                 */
                if (isset($value['max']) && is_numeric($data[$key]) && $data[$key] > $value['max']) {
                    self::raise('num too big');
                }

                /**
                 * 最小值参数
                 */
                if (isset($value['short']) && is_string($data[$key]) && mb_strlen($data[$key]) < $value['short']) {
                    self::raise('string too short');
                }

                /**
                 * 最大值参数
                 */
                if (isset($value['long']) && is_string($data[$key]) && mb_strlen($data[$key]) > $value['long']) {
                    self::raise('string too long');
                }

                /**
                 * 自定义的正则表达式
                 */
                if ($value['type'] == 'regx' && isset($value['regx']) && preg_match($value['regx'], $data[$key]) === false) {
                    self::raise();
                }
            }
        }

        /**
         * 如果为拒绝模式，所有不在过滤参数$param中的键值都将被删除
         */
        if ($this->mode == 'deny') {
            $allow = array_keys($param);
            $have  = array_keys($data);
            foreach ($have as $value) {
                if (!in_array($value, $allow)) {
                    unset($data[$value]);
                }
            }
        }
    }

    /**
     * @param bool|false $text
     */
    static public function raise($text = false) {
        if (self::$errorUrl) {
            header('Location: ' . self::$errorUrl);
        }
        if ($text) {
            exit($text);
        } else {
            exit('Client input param error!');
        }
    }

    /**
     * @param $param
     */
    public function get($param) {
        $this->_check($_GET, $param);
    }

    /**
     * @param $param
     */
    public function cookie($param) {
        $this->_check($_COOKIE, $param);
    }
}