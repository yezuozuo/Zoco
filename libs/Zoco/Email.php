<?php

namespace Zoco;

/**
 * Class Email
 *
 * @package Zoco
 */
class Email {
    /**
     * @var string
     */
    static $msg = 'test';

    /**
     * @var int
     */
    static $type = 1;

    /**
     * @var string
     */
    static $logout = 'justyehao@qq.com';

    static $from = '"From:1@qq.com"';

    /**
     * @param null $msg
     * @param null $logout
     */
    static public function send($msg = null, $logout = null, $from = null) {
        if (empty($msg)) {
            $msg = self::$msg;
        }

        if (empty($logout)) {
            $logout = self::$logout;
        }

        if (empty($from)) {
            $from = self::$from;
        }

        error_log($msg, self::$type, $logout, $from);
    }
}