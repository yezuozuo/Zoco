<?php

namespace Zoco;

/**
 * JS生成工具，可以生成常用的JavaScript代码
 * Class JS
 *
 * @package Zoco
 */
class JS {
    /**
     * @var string
     */
    static $head = "<script language=\"javascript\">\n";

    /**
     * @var string
     */
    static $foot = "</script>\n";

    /**
     * @var string
     */
    static $charset = 'utf-8';

    /**
     * @var bool
     */
    static $return = false;

    /**
     * 弹出信息框
     *
     * @param $str
     * @return bool|string
     */
    static public function alert($str) {
        return self::echojs("alert(\"$str\");");
    }

    /**
     * 输出JS
     *
     * @param            $js
     * @param bool|false $return
     * @return bool|string
     */
    static public function echojs($js, $return = false) {
        $out = self::charset($return);
        $out .= self::$head;
        $out .= $js;
        $out .= self::$foot;
        if ($return) {
            return $out;
        } else {
            echo $out;
        }
    }

    /**
     * @param bool|false $return
     * @return bool|string
     */
    static public function charset($return = false) {
        $out = '<meta http-equiv="Content-type" content="text/html; charset=' . self::$charset . '">';
        if ($return) {
            return $out;
        } else {
            echo $out;
        }
    }

    /**
     * 重定向URL
     *
     * @param $url
     * @return bool|string
     */
    static public function location($url) {
        return self::echojs("location.href='$url';");
    }

    /**
     * 历史记录返回
     *
     * @param     $msg
     * @param int $go
     * @return bool|string
     */
    static public function jsBack($msg, $go = -1) {
        if (!is_numeric($go)) {
            $go = -1;
        }

        return self::echojs("alert('$msg');\n history.go($go);\n");
    }

    /**
     * 父框架历史记录返回
     *
     * @param     $msg
     * @param int $go
     * @return bool|string
     */
    static public function parentJsBack($msg, $go = -1) {
        if (!is_numeric($go)) {
            $go = -1;
        }

        return self::echojs("alert('$msg');\n parent.history.go($go);\n");
    }

    /**
     * 父框架跳转
     *
     * @param $msg
     * @param $url
     * @return bool|string
     */
    static public function parentJsGoto($msg, $url) {
        return self::echojs("alert(\"$msg\");\n window.parent.location.href=\"$url\";");
    }

    /**
     * 跳转
     *
     * @param $msg
     * @param $url
     * @return bool|string
     */
    static public function jsGoto($msg, $url) {
        return self::echojs("alert('$msg');\n window.location.href=\"$url\";\n");
    }

    /**
     * 父框架重新载入
     *
     * @param $msg
     * @return bool|string
     */
    static public function jsParentReload($msg) {
        return self::echojs("alert('$msg');\n window.parent.location.reload();");
    }

    /**
     * 弹出信息并关闭窗口
     *
     * @param $msg
     * @return bool|string
     */
    static public function jsAlertClose($msg) {
        return self::echojs("alert('$msg');\n window.self.close();\n");
    }

    /**
     * 弹出确认，确定则进入$true指定的网址，否则转向$false指定的网址
     *
     * @param $msg
     * @param $true
     * @param $false
     * @return bool|string
     */
    static public function jsConfirm($msg, $true, $false) {
        $js = "if(confirm('$msg')) location.href=\"{$true}\";\n";
        $js .= "else location.href=\"$false\";\n";

        return self::echojs($js);
    }

    /**
     * 弹出确认，确定则进入$true指定的网址，否则返回
     *
     * @param $msg
     * @param $true
     * @return bool|string
     */
    static public function jsConfirmBack($msg, $true) {
        $js = "if(confirm('$msg')) location.href=\"{$true}\";\n";
        $js .= "else history.go(-1);\n";

        return self::echojs($js);
    }
}