<?php

namespace Zoco;

/**
 * Class RandomKey
 *
 * @package Zoco
 */
class RandomKey {
    /**
     * @return string
     */
    static public function getChineseCharacter($length = 5) {
        $zhchchr = '';
        for ($i = 0; $i < $length; $i++) {
            $unidec = rand(19968, 24869);
            $unichr = '&#' . $unidec . ';';
            $zhchchr .= mb_convert_encoding($unichr, 'UTF-8', 'HTML-ENTITIES');
        }

        return $zhchchr;
    }

    /**
     * @param     $uid
     * @param int $base
     * @return int
     */
    static public function idHash($uid, $base = 1000) {
        return intval($uid / $base);
    }

    /**
     * @param int $randLength
     * @return string
     */
    static public function randTime($randLength = 6) {
        list($usec, $sec) = explode(' ', microtime());
        $min = intval('1' . str_repeat('0', $randLength - 1));
        $max = intval(str_repeat('9', $randLength));

        return substr($sec, -5) . ((int)$usec * 100) . rand($min, $max);
    }

    /**
     * @param int  $length
     * @param null $seed
     * @return string
     */
    static public function randMd5($length = 8, $seed = null) {
        if (empty($seed)) {
            $seed = self::produceString(16);
        }

        return substr(md5($seed . rand(111111, 999999)), 0, $length);
    }

    /**
     * @param int        $length
     * @param bool|false $notO0
     * @return string
     */
    static public function produceString($length = 8, $notO0 = false, $isOnlyNumber = false, $isOnlyString = false) {
        $strings = 'ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $numbers = '1234567890';
        if ($notO0) {
            $strings = str_replace('O', '', $strings);
            $numbers = str_replace('0', '', $numbers);
        }
        if ($isOnlyNumber) {
            $pattern = $numbers;
        } else {
            if ($isOnlyString) {
                $pattern = $strings;
            } else {
                $pattern = $strings . $numbers;
            }
        }
        $max = strlen($pattern) - 1;
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, $max)};
        }

        return $key;
    }

    /**
     * @param bool|true $detail
     * @return string
     */
    static public function produceTime($detail = true) {
        if ($detail === true) {
            $temp = rand(1, 28);
            if ($temp < 10) {
                $res = '2015-' . date('m', time()) . '-0' . $temp;
            } else {
                $res = '2015-' . date('m', time()) . '-' . $temp;
            }

            return $res . ' ' . rand(0, 23) . ':' . rand(0, 59) . ':' . rand(0, 59);
        } else {
            $temp = rand(1, 28);
            if ($temp < 10) {
                $res = '2015-' . date('m', time()) . '-0' . $temp;
            } else {
                $res = '2015-' . date('m', time()) . '-' . $temp;
            }

            return $res;
        }
    }

    /**
     * @param int $sexType
     * @param int $nameType
     * @return string
     */
    static public function produceName($sexType = 0, $nameType = 3) {
        $nameArr = require LIBPATH . '/data/name.php';
        var_dump($nameArr);
        $countArr = array();
        if (!empty($nameArr)) {
            $countArr['surname'] = count($nameArr['surname']);//百家姓
            $countArr['man1']    = count($nameArr['man1']);      //男单
            $countArr['man2']    = count($nameArr['man2']);      //男双
            $countArr['women1']  = count($nameArr['women1']);  //女单
            $countArr['women2']  = count($nameArr['women2']);  //女双
        }

        $sexType  = empty($sexType) ? rand(1, 2) : $sexType;
        $nameType = empty($nameType) ? rand(1, 4) : $nameType;

        switch ($nameType) {
            case 1:
                $indexOffset = self::getArrayIndex($sexType, 1);
                $indexSingle = rand(0, $countArr[$indexOffset] - 1);
                $name        = $nameArr[$indexOffset][$indexSingle];
                break;
            case 2:
                $indexOffset = self::getArrayIndex($sexType, 2);
                $indexDouble = rand(0, $countArr[$indexOffset] - 1);
                $name        = $nameArr[$indexOffset][$indexDouble];
                break;
            case 3:
                $indexSurname = rand(0, $countArr['surname'] - 1);
                $indexOffset  = self::getArrayIndex($sexType, 2);
                $indexDouble  = rand(0, $countArr[$indexOffset] - 1);
                $name         = $nameArr['surname'][$indexSurname] . $nameArr[$indexOffset][$indexDouble];
                break;
            case 4:
                $indexSurname = rand(0, $countArr['surname'] - 1);
                $indexOffset  = self::getArrayIndex($sexType, 1);
                $indexDouble  = rand(0, $countArr[$indexOffset] - 1);
                $name         = $nameArr['surname'][$indexSurname] . $nameArr[$indexOffset][$indexDouble];
                break;
            default:
                $name = '无名氏';
                break;
        }

        return $name;
    }

    /**
     * @param int $sex
     * @param int $count
     * @return string
     */
    static private function getArrayIndex($sex = 1, $count = 2) {
        switch ($sex) {
            case 1://男
                $indexStr = 'man' . $count;
                break;
            case 2://女
                $indexStr = 'women' . $count;
                break;
            default:
                $indexStr = 'man' . $count;
                break;
        }

        return $indexStr;
    }

    /**
     * 生成不重复的随机数
     *
     * @param int $from
     * @param int $to
     * @param int $count
     */
    static public function zocoRandom($from = 1, $to = 100, $count = 6) {
        $numbers = range($from, $to);
        shuffle($numbers);
        $result = array_slice($numbers, 0, $count);

        return $result;
    }
}