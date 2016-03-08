<?php

namespace Zoco;

/**
 * 数值验证类，类中的方法都是静态的，用于检测一个变量是否符合某种规则，不符合返回false，符合返回原值
 * Class Validate
 *
 * @package Zoco
 */
class Validate {
    /**
     * @var array
     */
    static $regx = array(
        //邮箱
        'email'    => '/^[\w-\.]+@[\w-]+(\.(\w)+)*(\.(\w){2,4})$/',
        //手机号码
        'mobile'   => '/^(?:13\d|15\d|18\d)-?\d{5}(\d{3}|\*{3})$/',
        //固定电话带分机号
        'tel'      => '/^((0\d{2,3})-)(\d{7,8})(-(\d{1,4}))?$/',
        //固定电话不带分机号
        'phone'    => '/^\d{3}-?\d{8}|\d{4}-?\d{7}$/',
        //域名
        'domain'   => '/@([0-9a-z-_]+.)+[0-9a-z-_]+$/i',
        //日期
        'date'     => '/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2}$/',
        //日期时间
        'datetime' => '/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        //时间
        'time'     => '/^[0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        /*--------- 数字类型 --------------*/
        'int'      => '/^\d{1,11}$/',      //十进制整数
        'hex'      => '/^0x[0-9a-f]+$/i',  //16进制整数
        'bin'      => '/^[01]+$/',         //二进制
        'oct'      => '/^0[1-7]*[0-7]+$/', //8进制
        'float'    => '/^\d+\.[0-9]+$/',   //浮点型
        /*---------字符串类型 --------------*/
        //utf-8中文字符串
        'chinese'  => '/^[\x{4e00}-\x{9fa5}]+$/u',
        /*---------常用类型 --------------*/
        'english'  => '/^[a-z0-9_\.]+$/i',                 //英文
        'nickname' => '/^[\x{4e00}-\x{9fa5}a-z_\.]+$/ui',  //昵称，可以带英文字符和数字
        'realname' => '/^[\x{4e00}-\x{9fa5}]+$/u',         //真实姓名
        'password' => '/^[a-z0-9]{6,32}$/i',               //密码
        'area'     => '^0\d{2,3}$',                        //区号
        'version'  => '/^\d+\.\d+\.\d+$/',                 //版本号
    );

    /**
     * @param $version
     * @return bool
     */
    static public function isVersion($version) {
        return self::check('version', $version);
    }

    /**
     * 校验
     *
     * @param $ctype
     * @param $input
     * @return bool
     */
    static public function check($ctype, $input) {
        if (isset(self::$regx[$ctype])) {
            return self::regx(self::$regx[$ctype], $input);
        } else {
            return self::$ctype($input);
        }
    }

    /**
     * 正则校验
     *
     * @param $regx
     * @param $input
     * @return bool
     */
    static public function regx($regx, $input) {
        $n = preg_match($regx, $input, $match);
        if ($n == 0) {
            return false;
        } else {
            return $match[0];
        }
    }

    /**
     * 验证字符串格式
     *
     * @param $str
     * @return mixed
     */
    static public function string($str) {
        return filter_var($str, FILTER_DEFAULT);
    }

    /**
     * 验证是否是URL
     *
     * @param $str
     * @return mixed
     */
    static public function url($str) {
        return filter_var($str, FILTER_VALIDATE_URL);
    }

    /**
     * 过滤HTML，使参数为纯文本
     *
     * @param $str
     * @return mixed
     */
    static public function text($str) {
        return filter_var($str, FILTER_SANITIZE_STRING);
    }

    /**
     * 检测是否为gb2312中文字符串
     *
     * @param $str
     * @return bool
     */
    static public function chineseGb($str) {
        $n = preg_match("/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/", $str, $match);
        if ($n === 0) {
            return false;
        } else {
            return $match[0];
        }
    }

    /**
     * 检测是否为自然字符串（可是中文，字符串，下划线，数字），不包含特殊字符串，只支持utf-8或者gb2312
     *
     * @param        $str
     * @param string $encode
     * @return bool
     */
    static public function realString($str, $encode = 'utf8') {
        /**
         * utf-8
         */
        if ($encode == 'utf8') {
            $n = preg_match('/^[\x{4e00}-\x{9fa5}|a-z|0-9|A-Z]+$/u', $str, $match);
        } /**
         * gb2312
         */
        else {
            $n = preg_match("/^[" . chr(0xa1) . "-" . chr(0xff) . "|a-z|0-9|A-Z]+$/", $str, $match);
        }
        if ($n === 0) {
            return false;
        } else {
            return $match[0];
        }
    }

    /**
     * 检测是否是一个英文单词，不含空格和其他特殊字符
     *
     * @param        $str
     * @param string $other
     * @return bool
     */
    static public function word($str, $other = '') {
        $n = preg_match("/^([a-zA-Z_{$other}]*)$/", $str, $match);
        if ($n === 0) {
            return false;
        } else {
            return $match[0];
        }
    }

    /**
     * 检测是否是ascii码
     *
     * @param $value
     * @return bool
     */
    static public function ascii($value) {

        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $ord = ord(substr($value, $i, 1));
            echo $ord;
            if ($ord > 127) {
                return false;
            }
        }

        return $value;
    }

    /**
     * 检测是否是IP地址
     *
     * @param $value
     * @return bool
     */
    static public function ip($value) {
        $arr = explode('.', $value);
        if (count($arr) != 4) {
            return false;
        }
        foreach ($arr as $n) {
            $n = intval($n);
            if ($n < 1 || $n > 255) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检测身份证
     *
     * @param $number
     * @return bool
     */
    static public function idNumber($number) {
        if (!self::validationFilterIdCard($number)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    static private function validationFilterIdCard($id) {
        if (strlen($id) == 18) {
            return self::idCardCheckSum18($id);
        } elseif ((strlen($id) == 15)) {
            $id = self::idCard15to18($id);

            return self::idCardCheckSum18($id);
        } else {
            return false;
        }
    }

    /**
     * 18位身份证校验码有效性检查
     *
     * @param $id
     * @return bool
     */
    static public function idCardCheckSum18($id) {
        if (strlen($id) != 18) {
            return false;
        }
        $base = substr($id, 0, 17);
        if (self::idCardVerifyNumber($base) != strtoupper(substr($id, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     *
     * @param $base
     * @return bool
     */
    static public function idCardVerifyNumber($base) {
        if (strlen($base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $list     = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;

        for ($i = 0; $i < strlen($base); $i++) {
            $tmp = intval(substr($base, $i, 1)) * $factor[$i];
            $checksum += $tmp;
        }

        $mod    = $checksum % 11;
        $number = $list[$mod];

        return $number;
    }

    /**
     * 将15位身份证升级到18位
     *
     * @param $id
     * @return bool|string
     */
    static public function idCard15to18($id) {
        if (strlen($id) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($id, 12, 3), array('996', '997', '998', '999')) !== false) {
                $id = substr($id, 0, 6) . '18' . substr($id, 6, 9);
            } else {
                $id = substr($id, 0, 6) . '19' . substr($id, 6, 9);
            }
        }
        $id = $id . self::idCardVerifyNumber($id);

        return $id;
    }
}