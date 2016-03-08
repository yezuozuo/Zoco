<?php

namespace Zoco;

/**
 * 错误类
 * 错误输出、数据调试、中断程序运行
 * Class Error
 *
 * @package Zoco
 */
class Error extends \Exception {
    /**
     * 错误代码
     *
     * @var
     */
    public static $errorCode;
    /**
     * 遇到错误是否停止
     *
     * @var bool
     */
    static public $stop = true;
    /**
     * 是否输出html的错误
     * 在cli模式下不支持
     *
     * @var bool
     */
    static $echoHtml = false;
    /**
     * @var bool
     */
    static public $display = true;
    /**
     * 错误ID
     *
     * @var int
     */
    public $errorId;
    /**
     * 错误信息
     *
     * @var string
     */
    public $errorMsg;

    /**
     * 错误对象
     * 如果为int类型，则读取错误信息字典，否则设置错误字符串
     *
     * @param $error string
     */
    public function __construct($error) {
        if (is_numeric($error)) {
            if (empty($this->errorId)) {
                include LIBPATH . '/data/errorCode.php';
                $this->errorId = (int)$error;
                if (!isset(self::$errorCode[$this->errorId])) {
                    $this->errorMsg = self::$errorCode[$this->errorId];
                    parent::__construct($this->errorMsg, $error);
                }
            }
        } else {
            $this->errorId  = 0;
            $this->errorMsg = $error;
            parent::__construct($error);
        }

        global $php;

        /**
         * 如果定义了错误监听程序
         */
        if (isset($php->errorCall[$this->errorId])) {
            call_user_func($php->errorCall[$this->errorId], $error);
        }

        /**
         * 停止程序
         */
        if (self::$stop) {
            exit(Error::info('Zoco Error', $this->errorMsg));
        }
    }

    /**
     * 输出一条错误信息，并结束程序的运行
     *
     * @param $msg
     * @param $content
     * @return bool|string
     */
    static public function info($msg, $content) {
        if (!defined('DEBUG') || DEBUG == 'off' || self::$display == false) {
            return false;
        }
        $info = '';
        if (self::$echoHtml) {
            $info .= <<<HTMLS
<html>
<head>
<title>$msg</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{
	font-family:		Consolas, Courier New, Courier, monospace;
	font-size:			14px;
}
body {
	background-color:	#fff;
	margin:				40px;
	color:				#000;
}

#content  {
border:				#999 1px solid;
background-color:	#fff;
padding:			20px 20px 12px 20px;
line-height:160%;
}

h1 {
font-weight:		normal;
font-size:			14px;
color:				#990000;
margin: 			0 0 4px 0;
}
</style>
</head>
<body>
	<div id="content">
		<h1>$msg</h1>
		<p>$content</p><pre>
HTMLS;
        } else {
            $info .= "$msg: $content\n";
        }

        $trace = debug_backtrace();
        $info .= str_repeat('-', 100) . "\n";

        /**
         * 回溯错误
         */
        foreach ($trace as $key => $value) {
            if (empty($value['line'])) {
                $value['line'] = 0;
            }
            if (empty($value['class'])) {
                $value['class'] = '';
            }
            if (empty($value['type'])) {
                $value['type'] = '';
            }
            if (empty($value['file'])) {
                $value['file'] = 'unknown';
            }
            $info .= "#$key line:{$value['line']} call:{$value['class']}{$value['type']}{$value['function']}\t file:{$value['file']}\n";
        }
        $info .= str_repeat('-', 100) . "\n";
        if (self::$echoHtml) {
            $info .= '</pre></div></body></html>';
        }

        if (!self::$stop) {
            exit($info);
        } else {
            return $info;
        }
    }

    /**
     * 输出warning信息
     *
     * @param $title
     * @param $content
     */
    static public function warn($title, $content) {
        echo "<b>Warning </b>:" . $title . "<br/>\n";
        echo $content;
    }

    /**
     * 调试session和cookie
     */
    static public function debugSessionAndCookie() {
        echo '<pre>';
        echo '<h1>Session Data:</h1><hr/>';
        var_dump($_SESSION);
        echo '<h1>Cookies Data:</h1><hr/>';
        var_dump($_COOKIE);
        echo '</pre>';
    }

    /**
     * 调试get和post的数据
     */
    static public function debugGetAndPost() {
        echo '<pre>';
        echo '<h1>POST Data:</h1><hr/>';
        var_dump($_POST);
        echo '<h1>GET Data:</h1><hr/>';
        var_dump($_GET);
    }

    /**
     * 调试系统信息
     */
    static public function debugServer() {
        echo '<pre>';
        echo '<h1>Server Data:</h1><hr/>';
        var_dump($_SERVER);
        echo '<h1>ENV Data:</h1><hr/>';
        var_dump($_ENV);
        echo '<h1>REQUEST Data:</h1><hr/>';
        var_dump($_REQUEST);
        echo '</pre>';
    }

    /**
     * @return string
     */
    public function __toString() {
        if (!isset(self::$errorCode[$this->errorId])) {
            return 'Not defined Error.';
        }

        return self::$errorCode[$this->errorId];
    }
}