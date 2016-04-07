<?php

namespace Zoco;

/**
 * 客户端工具
 * 获取客户端IP，操作系统，浏览器，以及HTTP操作等等
 * Class Client
 *
 * @package Zoco
 * @method send($data)
 * @method close()
 */
class Client {
    /**
     * 发送下载说明
     *
     * @param $mime
     * @param $fileName
     */
    static public function download($mime, $fileName) {
        header("Content-type: $mime");
        header("Content-Disposition: attachment; filename=$fileName");
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    static public function getIP() {
        /**
         * 二进制安全比较字符串（不区分大小写）
         */
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = "unknown";
                }
            }
        }

        return $ip;
    }

    /**
     * 获取客户端浏览器信息
     *
     * @return bool
     */
    static public function getBrowser() {
        if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(Netscape[^;^)^(]*)|i")) {
            ;
        } else {
            if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(Opera[^;^)^(]*)|i")) {
                ;
            } else {
                if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(NetCaptor[^;^^()]*)|i")) {
                    ;
                } else {
                    if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(Firefox[0-9/\.^)^(]*)|i")) {
                        ;
                    } else {
                        if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(MSN[^;^)^(]*)|i")) {
                            ;
                        } else {
                            if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(Lynx[^;^)^(]*)|i")) {
                                ;
                            } else {
                                if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(WebTV[^;^)^(]*)|i")) {
                                    ;
                                } else {
                                    if ($browser = self::matchbrowser($_SERVER["HTTP_USER_AGENT"], "|(Chrome[^;^)^(]*)|i")) {
                                        ;
                                    } else {
                                        $browser = '其它';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $browser;
    }

    /**
     * @param $agent
     * @param $patten
     * @return bool
     */
    static private function matchBrowser($agent, $patten) {
        if (preg_match($patten, $agent, $tmp)) {
            return $tmp[1];
        } else {
            return false;
        }
    }

    /**
     * 获取客户端操作系统信息
     *
     * @return string
     */
    static public function getOS() {
        $os    = "";
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'win')) {
            if (strpos($agent, '95')) {
                $os = "Windows 95";
            } else {
                if (strpos($agent, '98')) {
                    $os = "Windows 98";
                } else {
                    if (strpos($agent, 'nt 5.0')) {
                        $os = "Windows 2000";
                    } else {
                        if (strpos($agent, 'nt 5.1')) {
                            $os = "Windows XP";
                        } else {
                            if (strpos($agent, 'nt 5.2')) {
                                $os = "Windows 2003";
                            } else {
                                if (strpos($agent, 'nt')) {
                                    $os = "Windows NT";
                                } else {
                                    $os = "Windows";
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (strpos($agent, 'linux')) {
                $os = "Linux";
            } else {
                if (strpos($agent, 'Mac')) {
                    $os = "Macintosh";
                } else {
                    if (strpos($agent, 'PowerPC')) {
                        $os = "PowerPC";
                    } else {
                        if (strpos($agent, 'NetBSD')) {
                            $os = "NetBSD";
                        } else {
                            if (strpos($agent, 'BSD')) {
                                $os = "BSD";
                            } else {
                                if (strpos($agent, 'FreeBSD')) {
                                    $os = "FreeBSD";
                                }
                            }
                        }
                    }
                }
            }

        }
        if ($os == '') {
            $os = "Unknown";
        }

        return $os;
    }

    /**
     * @return mixed
     */
    static public function requestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
}