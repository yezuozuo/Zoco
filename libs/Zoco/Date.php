<?php

namespace Zoco;

/**
 * Class Date
 *
 * @package Zoco
 */
class Date {
    /**
     * @var string
     */
    static $weekTwo = '周';

    /**
     * @var string
     */
    static $weekThree = '星期';

    /**
     * @param           $num
     * @param bool|true $two
     * @return string
     */
    static public function num2Week($num, $two = true) {
        if ($num == '6') {
            $num = '日';
        } else {
            $num = Tool::num2han($num + 1);
        }

        if ($two) {
            return self::$weekTwo . $num;
        } else {
            return self::$weekThree . $num;
        }
    }

    /**
     * @param        $param
     * @param null   $day
     * @param string $dateFormat
     * @return bool|string
     */
    static public function getDate($param, $day = null, $dateFormat = 'Y-m-d') {
        if (!empty($day)) {
            $time = strtotime($day);
        } else {
            $time = time();
        }

        return date($dateFormat, strtotime($param, $time));
    }
}