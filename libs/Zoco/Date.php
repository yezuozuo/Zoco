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
     * 把几日变成周
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
     * 根据参数获取日期
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

    /**
     * 计算一个月有几周
     * @param $year
     * @param $month
     * @return int
     */
    static public function weeksInMonth($year, $month){
        $wc = 0;
        $md = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if($fw = date("w", strtotime("{$year}-{$month}-1"))){
            $md += $fw - 7;
            $wc = 1;
        }
        return $wc + ceil($md / 7);
    }
}