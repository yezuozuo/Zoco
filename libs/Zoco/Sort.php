<?php

namespace Zoco;

/**
 * Class Sort
 *
 * @package Zoco
 */
class Sort {
    /**
     * 冒泡排序
     *
     * @param $array
     * @return mixed
     */
    static public function bubbleSort($array, $order = 'desc') {
        $len = count($array);
        if ($len <= 1) {
            return $array;
        }

        $count = count($array);

        if ($order == 'desc') {
            for ($i = 0; $i < $count; $i++) {
                for ($j = $count - 1; $j > $i; $j--) {
                    if ($array[$j] > $array[$j - 1]) {
                        $tmp           = $array[$j];
                        $array[$j]     = $array[$j - 1];
                        $array[$j - 1] = $tmp;
                    }
                }
            }
        } else {
            if ($order == 'asc') {
                for ($i = 0; $i < $count; $i++) {
                    for ($j = $count - 1; $j > $i; $j--) {
                        if ($array[$j] < $array[$j - 1]) {
                            $tmp           = $array[$j];
                            $array[$j]     = $array[$j - 1];
                            $array[$j - 1] = $tmp;
                        }
                    }
                }
            } else {
                echo 'order error.must be "desc" or "asc"';

                return false;
            }
        }

        return $array;
    }

    /**
     * 快速排序
     *
     * @param $arr
     * @return array
     */
    static public function quickSort($arr, $order = 'desc') {
        $len = count($arr);
        if ($len <= 1) {
            return $arr;
        }
        $key       = $arr[0];
        $left_arr  = array();
        $right_arr = array();

        if ($order == 'desc') {
            for ($i = 1; $i < $len; $i++) {
                if ($arr[$i] >= $key) {
                    $left_arr[] = $arr[$i];
                } else {
                    $right_arr[] = $arr[$i];
                }
            }
        } else {
            if ($order == 'asc') {
                for ($i = 1; $i < $len; $i++) {
                    if ($arr[$i] <= $key) {
                        $left_arr[] = $arr[$i];
                    } else {
                        $right_arr[] = $arr[$i];
                    }
                }
            } else {
                echo 'order error.must be "desc" or "asc"';

                return false;
            }
        }

        $left_arr  = self::quickSort($left_arr, $order);
        $right_arr = self::quickSort($right_arr, $order);

        return array_merge($left_arr, array($key), $right_arr);
    }

    /**
     * 选择排序
     *
     * @param $arr
     * @return mixed
     */
    static public function selectSort($arr, $order = 'desc') {
        $count = count($arr);

        if ($order == 'desc') {
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($arr[$i] < $arr[$j]) {
                        $tmp     = $arr[$i];
                        $arr[$i] = $arr[$j];
                        $arr[$j] = $tmp;
                    }
                }
            }
        } else {
            if ($order == 'asc') {
                for ($i = 0; $i < $count; $i++) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($arr[$i] > $arr[$j]) {
                            $tmp     = $arr[$i];
                            $arr[$i] = $arr[$j];
                            $arr[$j] = $tmp;
                        }
                    }
                }
            } else {
                echo 'order error.must be "desc" or "asc"';

                return false;
            }
        }

        return $arr;
    }

    /**
     * 插入排序
     *
     * @param $arr
     * @return mixed
     */
    static public function insertSort($arr, $order = 'desc') {
        $count = count($arr);

        if ($order == 'desc') {
            for ($i = 1; $i < $count; $i++) {
                $tmp = $arr[$i];
                $j   = $i - 1;
                while ($arr[$j] < $tmp && $j >= 0) {
                    $arr[$j + 1] = $arr[$j];
                    $arr[$j]     = $tmp;
                    $j--;
                }
            }
        } else {
            if ($order == 'asc') {
                for ($i = 1; $i < $count; $i++) {
                    $tmp = $arr[$i];
                    $j   = $i - 1;
                    while ($arr[$j] > $tmp) {
                        $arr[$j + 1] = $arr[$j];
                        $arr[$j]     = $tmp;
                        $j--;
                    }
                }
            } else {
                echo 'order error.must be "desc" or "asc"';

                return false;
            }
        }

        return $arr;
    }

    /**
     * 二分法查找
     *
     * @param $arr
     * @param $searchVal
     * @return mixed
     */
    static public function halfSearch($arr, $searchVal) {
        $low  = 0;
        $high = count($arr) - 1;
        while ($low <= $high) {
            $mid    = (int)ceil(($low + $high) / 2);
            $midVal = $arr[$mid];
            if ($midVal < $searchVal) {
                $low = $mid + 1;
            } else {
                if ($midVal > $searchVal) {
                    $high = $mid - 1;
                } else {
                    return $midVal;
                }
            }
        }

        return $arr;
    }
}