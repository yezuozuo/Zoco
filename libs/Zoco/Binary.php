<?php

namespace Zoco;

/**
 * 二进制打包类
 * Class Binary
 *
 * @package Zoco
 */
class Binary {
    /**
     * 内容
     *
     * @var string
     */
    public $body;

    /**
     * 大端还是小端
     *
     * @var bool
     */
    public $bigEndian = true;

    /**
     * @param string $body
     */
    public function __construct($body = '') {
        $this->body = $body;
    }

    static public function binaryFormat($valArr, $formatStr) {
        if (!is_array($valArr)) {
            return false;
        }
        $param = array_merge(array($formatStr), $valArr);
        $ret   = call_user_func_array('pack', $param);

        return $ret;
    }

    /**
     * @param $i
     */
    public function addUShort($i) {
        if ($this->bigEndian) {
            $this->body .= pack('n', $i);
        } else {
            $this->body .= pack('v', $i);
        }
    }

    /**
     * @param $i
     */
    public function addUint($i) {
        if ($this->bigEndian) {
            $this->body .= pack('N', $i);
        } else {
            $this->body .= pack('V', $i);
        }
    }

    /**
     * @param $uint64Str
     */
    public function addUInt64($uint64Str) {
        /**
         * 2^32 = 4294967296
         */

        /**
         * 对$uint64Str 取模
         */
        $low  = bcmod($uint64Str, '4294967296');
        $high = bcdiv($uint64Str, '4294967296', 0);
        if ($this->bigEndian) {
            $this->body .= pack('NN', $high, $low);
        } else {
            $this->body .= pack('VV', $low, $high);
        }
    }

    /**
     * @param $i
     */
    public function addInt($i) {
        if ($this->bigEndian) {
            $this->body .= pack('N', $i);
        } else {
            $this->body .= pack('V', $i);
        }
    }

    /**
     * @param $s
     */
    public function addTinyString($s) {
        if ($s == '') {
            $this->body .= pack('C', 0);
        } else {
            $this->body .= pack('C', strlen($s)) . $s;
        }
    }

    /**
     * @param $s
     */
    public function addShortString($s) {
        if ($this->bigEndian) {
            if ($s == '') {
                $this->body .= pack('n', 0);
            } else {
                $this->body .= pack('n', strlen($s)) . $s;
            }
        } else {
            if ($s == '') {
                $this->body .= pack('v', 0);
            } else {
                $this->body .= pack('v', $s);
            }
        }
    }

    /**
     * @param $s
     */
    public function addLongString($s) {
        if ($s == '') {
            $this->body .= "\0\0\0\0\0";
        } else {
            if ($this->bigEndian) {
                $this->body .= pack('N', strlen($s)) . $s;
            } else {
                $this->body .= pack('V', strlen($s)) . $s;
            }
        }
    }

    /**
     * @param     $s
     * @param int $len
     */
    public function addString($s, $len = 0) {
        if ($len > 0) {
            $this->body .= pack('a' . ($len - 1) . 'x', $s);
        } else {
            $this->body .= $s . chr(0);
        }
    }

    /**
     * @param $f
     */
    public function addFloat($f) {
        $this->body .= pack('f', $f);
    }

    public function addDouble($d) {
        $this->body .= pack('d', $d);
    }

    /**
     * 获得ascii码
     *
     * @return null
     */
    public function getUChar() {
        $ret = @unpack('Cret', $this->body);
        if ($ret == false) {
            return null;
        }
        $this->body = substr($this->body, 1);

        return $ret['ret'];
    }

    /**
     * @return null
     */
    public function getUShort() {
        if ($this->bigEndian) {
            $ret = @unpack('nret', $this->body);
        } else {
            $ret = @unpack('vret', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $this->body = substr($this->body, 2);

        return $ret['ret'];
    }

    /**
     * @return int|null
     */
    public function getUInt() {
        if ($this->bigEndian) {
            $ret = @unpack('nhigh/nlow', $this->body);
        } else {
            $ret = @unpack('vlow/vhigh', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $this->body = substr($this->body, 4);

        return ($ret['high'] << 16) | $ret['low'];
    }

    /**
     * @return null|string
     */
    public function getUInt64() {
        if ($this->bigEndian) {
            $ret = unpack('Nhigh/Nlow', $this->body);
        } else {
            $ret = unpack('Vlow/Vhigh', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $uInt64     = bcadd(bcmul($ret['high'], '4294967296', 0), $ret['low']);
        $this->body = substr($this->body, 8);

        return $uInt64;
    }

    /**
     * @return null
     */
    public function getInt() {
        if ($this->bigEndian) {
            $ret = @unpack('Nret', $this->body);
        } else {
            $ret = @unpack('Vret', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $this->body = substr($this->body, 4);

        return $ret['ret'];
    }

    /**
     * @param     $len
     * @param int $offset
     * @return string
     */
    public function getData($len, $offset = 0) {
        $ret        = substr($this->body, $offset, $len);
        $this->body = substr($this->body, $len + $offset);

        return $ret;
    }

    /**
     * @param bool|false $end0
     * @return null|string
     */
    public function getString($end0 = false) {
        $ret = @unpack('Clen', $this->body);
        if ($ret == false) {
            return null;
        }
        $rets       = substr($this->body, 1, $ret['len']);
        $this->body = substr($this->body, $ret['len'] + 1);

        /**
         * 长度为0时substr会返回false，需要特殊处理
         */
        if ($ret['len'] == 0) {
            return "";
        }

        return $end0 ? substr($rets, 0, -1) : $rets;
    }

    /**
     * @param bool|false $end0
     * @return null|string
     */
    public function getShortString($end0 = false) {
        if ($this->bigEndian) {
            $ret = @unpack('nlen', $this->body);
        } else {
            $ret = @unpack('vlen', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $rets       = substr($this->body, 2, $ret['len']);
        $this->body = substr($this->body, $ret['len'] + 2);

        /**
         * 长度为0时substr会返回false，需要特殊处理
         */
        if ($ret['len'] == 0) {
            return "";
        }

        return $end0 ? substr($rets, 0, -1) : $rets;
    }

    /**
     * @param bool|false $end0
     * @return null|string
     */
    public function getInt32String($end0 = false) {
        if ($this->bigEndian) {
            $ret = @unpack('Nlen', $this->body);
        } else {
            $ret = @unpack('Vlen', $this->body);
        }

        if ($ret == false) {
            return null;
        }

        $rets       = substr($this->body, 4, $ret['len']);
        $this->body = substr($this->body, $ret['len'] + 4);

        /**
         * 长度为0时substr会返回false，需要特殊处理
         */
        if ($ret['len'] == 0) {
            return "";
        }

        return $end0 ? substr($rets, 0, -1) : $rets;
    }

    /**
     * @param int $len
     * @param int $dropLen
     * @return null|string
     */
    public function getStdString($len = 0, $dropLen = 0) {
        $p = strpos($this->body, "\0");
        if ($p === false && $len == 0) {
            return null;
        }

        if ($len == 0) {
            $rets       = substr($this->body, 0, $p);
            $this->body = substr($this->body, $p + 1);
        } else {
            $rets = substr($this->body, 0, (($p < $len) ? $p : ($len - $dropLen)));
        }

        return $rets;
    }

    /**
     * @param $len
     * @return string
     */
    public function getFixedString($len) {
        if ($len >= strlen($this->body)) {
            $data       = $this->body;
            $this->body = '';
        } else {
            $data       = substr($this->body, 0, $len);
            $this->body = substr($this->body, $len);
        }

        return $data;
    }

    /**
     * @return null
     */
    public function getFloat() {
        $ret = @unpack('fret', $this->body);
        if ($ret == false) {
            return null;
        }
        $this->body = substr($this->body, 12);

        return $ret['ret'];
    }

    /**
     * @return null
     */
    public function getDouble() {
        $ret = @unpack('dret', $this->body);
        if ($ret == false) {
            return null;
        }
        $this->body = substr($this->body, 8);

        return $ret['ret'];
    }

    /*
     * @param $valArr 值的数组对应结构化的数组
     * @param $formatStr 格式化字符串
     * @return bool|mixed
     */

    /**
     * 重新设置
     *
     * @return string
     */
    public function getReset() {
        $ret        = $this->body;
        $this->body = '';

        return $ret;
    }
}