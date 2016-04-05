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

    /**
     * 给定一个日期，获取其本月的第一天
     * @param $date
     * @return bool|string
     */
    static public function getCurMonthFirstDay($date) {
        return date('Y-m-01', strtotime($date));
    }

    /**
     * 给定一个日期，获取其本月的最后一天
     * @param $date
     * @return bool|string
     */
    static public function getCurMonthLastDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month -1 day'));
    }

    /**
     * 给定一个日期，获取其下月的第一天
     * @param $date
     * @return bool|string
     */
    static public function getNextMonthFirstDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
    }

    /**
     * 给定一个日期，获取其下月的最后一天
     * @param $date
     * @return bool|string
     */
    static public function getNextMonthLastDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +2 month -1 day'));
    }

    /**
     * 给定一个日期，获取其上月的第一天
     * @param $date
     * @return bool|string
     */
    static public function getPrevMonthFirstDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 month'));
    }

    /**
     * 给定一个日期，获取其上月的最后一天
     * @param $date
     * @return bool|string
     */
    static public function getPrevMonthLastDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 day'));
    }
}