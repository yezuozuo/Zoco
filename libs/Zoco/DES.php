<?php

namespace Zoco;

/**
 * 对称加密算法类
 * 支持密钥：64/128/256 bit（字节长度8/16/32）
 * 支持算法：DES/AES（根据密钥长度自动匹配使用：DES:64bit AES:128/256bit）
 * 支持模式：CBC/ECB/OFB/CFB
 * 密文编码：base64字符串/十六进制字符串/二进制字符串流
 * 填充方式: PKCS5Padding（DES）
 * Class DES
 *
 * @package Zoco
 */
class DES {
    const PADDING_NONE  = 0;
    const PADDING_PKCS5 = 1;
    const PADDING_PKCS7 = 2;
    /**
     * @var bool
     */
    public $base64Encoding = true;
    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var null
     */
    protected $cipher;
    /**
     * @var null
     */
    protected $iv = null;
    /**
     * @var
     */
    protected $blockSize;
    /**
     * @var bool|int
     */
    protected $padding = false;

    /**
     * @param           $key
     * @param null      $cipher
     * @param string    $mode
     * @param bool|true $base64
     * @param int       $padding
     * @throws \Exception
     */
    public function __construct($key, $cipher = null, $mode = 'cbc', $base64 = true, $padding = self::PADDING_NONE) {
        if (!function_exists('mcrypt_create_iv')) {
            throw new \Exception(__CLASS__ . ' require mcrypt extension.');
        }

        $this->key = $key;

        if (!$cipher) {
            $this->cipher = self::getCipher($key);
        } else {
            $this->cipher = $cipher;
        }

        $this->base64Encoding = $base64;
        $this->padding        = $padding;

        switch (strtolower($mode)) {
            case 'ofb':
                $this->mode = MCRYPT_MODE_OFB;
                break;
            case 'cfb':
                $this->mode = MCRYPT_MODE_CFB;
                break;
            case 'ecb':
                $this->mode = MCRYPT_MODE_ECB;
                break;
            default:
                $this->mode = MCRYPT_MODE_CBC;
        }
    }

    /**
     * @param $key
     * @return string
     * @throws \Exception
     */
    static public function getCipher($key) {
        switch (strlen($key)) {
            case 8:
                $mcrypt = MCRYPT_DES;
                break;
            case 16:
                $mcrypt = MCRYPT_RIJNDAEL_128;
                break;
            case 32:
                $mcrypt = MCRYPT_RIJNDAEL_256;
                break;
            default:
                throw new \Exception('des key size must be 8/16/32');
        }

        return $mcrypt;
    }

    public function createIV() {
        $source   = PHP_OS == 'WINNT' ? MCRYPT_RAND : MCRYPT_DEV_RANDOM;
        $this->iv = mcrypt_create_iv($this->blockSize, $source);
    }

    /**
     * @return null
     */
    public function getIV() {
        return $this->iv;
    }

    /**
     * @param $iv
     */
    public function setIV($iv) {
        $this->iv        = $iv;
        $this->blockSize = mcrypt_get_block_size($this->cipher);
    }

    /**
     * @param $str
     * @return string
     */
    public function encode($str) {
        /**
         * 是否补码
         */
        if ($this->padding) {
            $str = self::PADDING_PKCS5 == $this->padding ? $this->addPKCS5Padding($str) : $this->addPKCS7Padding($str);
        }

        /**
         * 是否进行BASE64编码
         */
        if ($this->base64Encoding) {
            $_str = base64_encode($str);
        } else {
            $_str = $str;
        }

        return mcrypt_encrypt($this->cipher, $this->key, $_str, $this->mode, $this->iv);
    }

    /**
     * 描述一种利用从口令派生出来的安全密钥加密字符串的方法。使用MD2或MD5 从口令中派生密钥，并采用DES-CBC模式加密。主要用于加密从一个计算机传送到另一个计算机的私人密钥，不能用于加密消息
     *
     * @param $source
     * @return string
     */
    public function addPKCS5Padding($source) {
        $pad = $this->blockSize - (strlen($source) % $this->blockSize);

        return $source . str_repeat(chr($pad), $pad);
    }

    /**
     * 定义一种通用的消息语法，包括数字签名和加密等用于增强的加密机制，PKCS7与PEM兼容，所以不需其他密码操作，就可以将加密的消息转换成PEM消息
     *
     * @param $source
     * @return string
     */
    public function addPKCS7Padding($source) {
        $pad = $this->blockSize - (strlen($source) % $this->blockSize);

        if ($pad <= $this->blockSize) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }

        return $source;
    }

    /**
     * @param $str
     * @return bool|string
     */
    public function decode($str) {
        $ret = mcrypt_decrypt($this->cipher, $this->key, $str, $this->mode, $this->iv);

        /**
         * 去除补码
         */
        if ($this->padding) {
            $ret = self::PADDING_PKCS5 == $this->padding ? $this->stripPKCS5Padding($ret) : $this->stripPKCS7Padding($ret);
        }

        /**
         * 是否使用BASE64编码
         */
        if ($this->base64Encoding) {
            return base64_decode($ret);
        } else {
            return $ret;
        }
    }

    /**
     * @param $source
     * @return bool|string
     */
    public function stripPKCS5Padding($source) {
        $pad = ord($source{strlen($source) - 1});
        if ($pad > strlen($source)) {
            return false;
        }

        if (strspn($source, chr($pad), strlen($source) - $pad) != $pad) {
            return false;
        }

        $ret = substr($source, 0, -1 * $pad);

        return $ret;
    }

    /**
     * @param $source
     * @return string
     */
    public function stripPKCS7Padding($source) {
        $char = substr($source, -1, 1);
        $num  = ord($char);

        if ($num > 0) {
            return $source;
        }

        $len = strlen($source);

        for ($i = $len - 1; $i >= $len - $num; $i--) {
            if (ord(substr($source, $i, 1)) != $num) {
                return $source;
            }
        }

        $source = substr($source, 0, -$num);

        return $source;
    }
}