<?php

namespace Zoco;

/**
 * 附加工具集合
 * Class Tool
 *
 * @package Zoco
 */
class Tool {
    /**
     * @var string
     */
    static public $urlKeyJoin = '=';
    /**
     * @var string
     */
    static public $urlParamJoin = '&';
    /**
     * @var string
     */
    static public $urlPrefix = '';
    /**
     * @var string
     */
    static public $urlAddEnd = '';
    /**
     * @var array
     */
    static $number = array('〇', '一', '二', '三', '四', '五', '六', '七', '八', '九');

    /**
     * 数字转为汉字
     *
     * @param $numStr
     * @return mixed
     */
    static public function num2han($numStr) {
        return str_replace(range(0, 9), self::$number, $numStr);
    }

    /**
     * 递归扫描目录下的文件
     *
     * @param $dir
     * @return array|bool
     */
    static public function scandir($dir) {
        if (function_exists('scandir')) {
            return scandir($dir);
        } else {
            $dh = opendir($dir);
            while (false !== ($fileName = readdir($dh))) {
                if ($fileName == '.' || $fileName == '..') {
                    continue;
                }
                $files[] = $fileName;
            }
            sort($files);
        }

        return true;
    }

    /**
     * 将PHP变量导出为文件内容
     *
     * @param $var
     * @return string
     */
    static public function export($var) {
        return "<?php\n return " . var_export($var, true) . ';';
    }

    /**
     * 解析URI
     *
     * @param $url
     * @return mixed
     */
    static public function uri($url) {
        $res                = parse_url($url);
        $return['protocol'] = @$res['scheme'];
        $return['host']     = @$res['host'];
        $return['port']     = @$res['port'];
        $return['user']     = @$res['user'];
        $return['pass']     = @$res['pass'];
        $return['path']     = @$res['path'];
        $return['id']       = @$res['fragment'];
        parse_str(@$res['query'], @$return['params']);

        return $return;
    }

    /**
     * 多久之前
     *
     * @param $dateTime
     * @return string
     */
    static public function howLongAgo($dateTime) {
        $timeStamp = strtotime($dateTime);
        $seconds   = time();

        $time = date('Y', $seconds) - date('Y', $timeStamp);
        if ($time > 0) {
            if ($time == 1) {
                return '去年';
            } else {
                return $time . '年前';
            }
        }

        $time = date('m', $seconds) - date('m', $timeStamp);
        if ($time > 0) {
            if ($time > 0) {
                if ($time == 1) {
                    return '上月';
                } else {
                    return $time . '个月前';
                }
            }
        }

        $time = date('d', $seconds) - date('d', $timeStamp);
        if ($time > 0) {
            if ($time == 1) {
                return '昨天';
            } else {
                if ($time == 2) {
                    return '前天';
                } else {
                    return $time . '天前';
                }
            }
        }

        $time = date('H', $seconds) - date('H', $timeStamp);
        if ($time >= 1) {
            return $time . '小时前';
        }

        $time = date('i', $seconds) - date('i', $timeStamp);
        if ($time >= 1) {
            return $time . '分钟前';
        }

        $time = date('s', $seconds) - date('s', $timeStamp);

        return $time . '秒前';
    }

    /**
     * URL合并
     *
     * @param      $key
     * @param      $value
     * @param null $ignore
     * @param null $urls
     * @return string
     */
    static public function urlMerge($key, $value, $ignore = null, $urls = null) {
        if ($urls === null) {
            $urls = $_GET;
        }
        $urls = array_merge($urls, array_combine(explode(',', $key), explode(',', $value)));
        if ($ignore !== null) {
            $ignores = explode(',', $ignore);
            foreach ($ignores as $ig) {
                unset($urls[$ig]);
            }
        }
        if (self::$urlPrefix == '') {
            $qm = strpos($_SERVER['REQUEST_URI'], '?');
            if ($qm !== false) {
                $prefix = substr($_SERVER['REQUEST_URI'], 0, $qm + 1);
            } else {
                $prefix = $_SERVER['REQUEST_URI'] . '?';
            }
        } else {
            $prefix = self::$urlPrefix;
        }

        return $prefix . self::combineQuery($urls) . self::$urlAddEnd;
    }

    /**
     * 合并URL字串，parse_query的反向函数
     *
     * @param $urls
     * @return string
     */
    static public function combineQuery($urls) {
        $url = array();
        foreach ($urls as $key => $value) {
            if (!empty($key)) {
                $url[] = $key . self::$urlKeyJoin . urlencode($value);
            }
        }

        return implode(self::$urlParamJoin, $url);
    }

    /**
     * URL解析到REQUEST
     *
     * @param $url
     * @param $request
     */
    static public function urlParseInto($url, &$request) {
        $url = str_replace(self::$urlAddEnd, '', $url);
        if (self::$urlKeyJoin == self::$urlParamJoin) {
            $urls = explode(self::$urlParamJoin, $url);
            $c    = intval(count($urls) / 2);
            for ($i = 0; $i < $c; $i++) {
                $request[$urls[$i * 2]] = $urls[$i * 2 + 1];
            }
        } else {
            $urls = explode(self::$urlParamJoin, $url);
            foreach ($urls as $u) {
                $us              = explode(self::$urlKeyJoin, $u);
                $request[$us[0]] = $us[1];
            }
        }
    }

    /**
     * 数组编码转换
     *
     * @param $inCharset
     * @param $outCharset
     * @param $data
     * @return array
     */
    static public function arrayIconv($inCharset, $outCharset, $data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = self::arrayIconv($inCharset, $outCharset, $value);
                } else {
                    $value = iconv($inCharset, $outCharset, $value);
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * 数组饱满度
     *
     * @param $array
     * @return int
     */
    static public function arrayFullness($array) {
        if (count($array) == 0) {
            return 0;
        }
        $nulls = 0;
        foreach ($array as $value) {
            if (empty($value) || intval($value) < 0) {
                $nulls++;
            }
        }

        return 100 - intval($nulls / count($array) * 100);
    }

    /**
     * 根据生日中的月份和日期来计算所属星座
     *
     * @param $birthMonth
     * @param $birthDay
     * @return mixed
     */
    static public function getConstellation($birthMonth, $birthDay) {
        /**
         * 判断的时候，为避免出现1和true的疑惑，或是判断语句始终为真的问题，这里统一处理成字符串形式
         */
        $birthMonth        = strval($birthMonth);
        $constellationName = array('水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座');

        if ($birthDay <= 22) {
            if ('1' !== $birthMonth) {
                $constellation = $constellationName[$birthMonth - 2];
            } else {
                $constellation = $constellationName[11];
            }
        } else {
            $constellation = $constellationName[$birthMonth - 1];
        }

        return $constellation;
    }

    /**
     * 根据生日中的年份来计算所属生肖
     *
     * @param        $birthYear
     * @param string $format
     * @return mixed
     */
    static public function getAnimal($birthYear, $format = '1') {
        /**
         * 1900是子鼠年
         */
        if ($format == '2') {
            $animal = array('子鼠', '丑牛', '寅虎', '卯兔', '辰龙', '巳蛇', '午马', '未羊', '申猴', '酉鸡', '戌狗', '亥猪');
        } else {
            $animal = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
        }
        $myAnimal = ($birthYear - 1900) % 12;

        return $animal[$myAnimal];
    }

    /**
     * 发送一个UDP包
     *
     * @param     $serverIp
     * @param     $serverPort
     * @param     $data
     * @param int $timeout
     */
    static public function sendUDP($serverIp, $serverPort, $data, $timeout = 30) {
        $client = stream_socket_client("udp://$serverIp:$serverPort", $errno, $errstr, $timeout);
        if (!$client) {
            echo "ERROR: $errno - $errstr" . BL . NL;
        } else {
            fwrite($client, $data);
            fclose($client);
        }
    }

    /**
     * 复制目录
     *
     * @param $sourceDir
     * @param $destDir
     * @return bool
     */
    static public function dirCopy($sourceDir, $destDir) {
        if (is_dir($sourceDir)) {
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777);
            }
            $handle = opendir($sourceDir);
            while (false !== ($fileName = readdir($handle))) {
                if ($fileName != '.' && $fileName != '..') {
                    self::dirCopy($sourceDir . '/' . $fileName, $destDir . '/' . $fileName);
                }
                closedir($handle);

                return true;
            }
        } else {
            copy($sourceDir, $destDir);

            return true;
        }

        return true;
    }

    /**
     * 文件追加
     *
     * @param        $log
     * @param string $file
     */
    static public function fileAppend($log, $file = '') {
        if (empty($file)) {
            $file = '/tmp/zoco.log';
        }
        if (!is_string($log)) {
            $log = var_export($log, true);
        }
        if (self::endChar($log) !== "\n") {
            $log .= "\n";
        }
        file_put_contents($file, $log, FILE_APPEND);
    }

    /**
     * 获取字符串最后一位
     *
     * @param $string
     * @return mixed
     */
    static public function endChar($string) {
        return $string[strlen($string) - 1];
    }

    /**
     * 返回一共有多少天
     *
     * @param      $beginDate
     * @param null $endDate
     * @return int
     */
    static public function daysNumber($beginDate, $endDate = null) {
        $begin = strtotime($beginDate);
        if ($endDate === null) {
            $endDate = date('Ymd');
        }
        $end  = strtotime($endDate);
        $days = ($end - $begin) / (3600 * 24) + 1;

        return intval($days);
    }

    /**
     * 消除$source文件中的所有空格
     *
     * @param      $sourceFile
     * @param null $destFile
     * @return bool
     */
    static public function trimAll($sourceFile, $destFile = null) {
        if (!is_file($sourceFile)) {
            echo "$sourceFile not a real file!";

            return false;
        }
        if ($destFile === null) {
            $destFile = $sourceFile . '.trim';
        }
        if (file_exists($destFile)) {
            unlink($destFile);
        }
        $handle = fopen($sourceFile, 'r');
        if ($handle) {
            while (!feof($handle)) {
                $buffer   = fgets($handle, 4096);
                $buffer_s = self::trimRow($buffer);
                file_put_contents($destFile, $buffer_s, FILE_APPEND);
            }
        } else {
            echo "cannot open file $sourceFile!";

            return false;
        }

        return true;
    }

    /**
     * 删除一行的空格，用来处理非常规的文本
     *
     * @param $str
     * @return mixed|string
     */
    static private function trimRow($str) {
        if ($str == "\r\n") {
            return "";
        }

        $before = array(" ", " ", "  ", "   ", "    ", "\t");
        $after  = array("", "", "", "", "", "");

        return str_replace($before, $after, $str);
    }

    /**
     * @param $num
     * @return array|bool|string
     */
    static public function numberFormat($num) {
        if (!is_numeric($num)) {
            return false;
        }
        $rvalue = '';
        $num    = explode('.', $num);
        $rl     = !isset($num['1']) ? '' : $num['1'];
        $j      = strlen($num[0]) % 3;
        $sl     = substr($num[0], 0, $j);
        $sr     = substr($num[0], $j);
        $i      = 0;
        while ($i <= strlen($sr)) {
            $rvalue = $rvalue . ',' . substr($sr, $i, 3);
            $i      = $i + 3;
        }
        $rvalue = $sl . $rvalue;
        $rvalue = substr($rvalue, 0, strlen($rvalue) - 1);
        $rvalue = explode(',', $rvalue);
        if ($rvalue[0] == 0) {
            array_shift($rvalue);
        }
        $rv = $rvalue[0];
        for ($i = 1; $i < count($rvalue); $i++) {
            $rv = $rv . ',' . $rvalue[$i];
        }
        if (!empty($rl)) {
            $rvalue = $rv . '.' . $rl;
        } else {
            $rvalue = $rv;
        }

        return $rvalue;
    }

    /**
     * 获取当前时间
     *
     * @return float
     */
    static public function getCurrentTime() {
        list($msec, $sec) = explode(' ', microtime());

        return (float)$msec + (float)$sec;
    }

    /**
     * 一种加密方式
     *
     * @param string $string
     * @param string $skey
     * @return mixed
     */
    static public function bubbleEncode($string = '', $skey = 'sjp-Alan') {
        $strArr   = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value) {
            $key < $strCount && $strArr[$key] .= $value;
        }

        return str_replace(array('=', '+', '/'), array('o0o0o', 'o000o', 'oo00o'), join('', $strArr));
    }

    /**
     * @param string $string
     * @param string $skey
     * @return string
     */
    static public function bubbleDecode($string = '', $skey = 'sjp-Alan') {
        $strArr   = str_split(str_replace(array('o0o0o', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value) {
            $key <= $strCount && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        }

        return base64_decode(join('', $strArr));
    }

    /**
     * 查看函数的性能
     *
     * @param $func
     */
    static public function showCost($func) {
        $_t = microtime(true);
        $_m = memory_get_usage(true);
        call_user_func($func);
        $t = round((microtime(true) - $_t) * 1000, 3);
        $m = memory_get_usage(true) - $_m;
        echo "cost Time: {$t}ms, Memory=" . self::getHumanSize($m) . "\n";
    }

    /**
     * 获取容量
     *
     * @param     $n
     * @param int $round
     * @return string
     */
    static public function getHumanSize($n, $round = 3) {
        if ($n > 1024 * 1024 * 1024) {
            return round($n / (1024 * 1024 * 1024), $round) . "G";
        } elseif ($n > 1024 * 1024) {
            return round($n / (1024 * 1024), $round) . "M";
        } elseif ($n > 1024) {
            return round($n / (1024), $round) . "K";
        } else {
            return $n;
        }
    }

    /**
     * @param $argv
     * @return array
     */
    static public function formatInputDate($argv) {
        $beginTime = isset($argv[1]) ? strtotime($argv[1]) : strtotime(date('Ymd', strtotime("-1day")));
        $endTime   = isset($argv[2]) ? strtotime($argv[2]) : strtotime(date('Ymd'));

        return array(
            'beginTime' => $beginTime,
            'endTime'   => $endTime,
        );
    }
}