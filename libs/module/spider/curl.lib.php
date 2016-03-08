<?php

/**
 * 命令行颜色输出
 */
$colors['red']     = "\33[31m";
$colors['green']   = "\33[32m";
$colors['yellow']  = "\33[33m";
$colors['end']     = "\33[0m";
$colors['reverse'] = "\33[7m";
$colors['purple']  = "\33[35m";

/**
 * 重试次数
 */
$retry_config = 5;

/**
 * 连接超时
 */
$connect_timeout_config = 10;

/**
 * 抓取网页超时
 */
$fetch_timeout_config = 10;

/**
 * 下载文件超时
 */
$download_timeout_config = 60;

/*
 * 针对指定域名设置referer(通常是用于下载图片)
 * 默认使用空referer，一般不会有问题
 * eg: $referer_config = array(
 *        'img_domain'=>'web_domain',
 *        'e.hiphotos.baidu.com'=>'http://hi.baidu.com/');
*/
$referer_config = array(
    'img1.51cto.com' => 'blog.51cto.com',
    '360doc.com'     => 'www.360doc.com',
);

/*
 * 针对指定域名设置User-agent
 * 默认使用百度蜘蛛的UA，拒绝百度UA的网站极少
 * eg: $useragent_config = array(
 *        'web_domain'=>'user agent',
 *        'www.xxx.com'=>'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)');
*/
$useragent_config = array(
    'hiphotos.baidu.com' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)',
);

/*
 * 如果机器有多个IP地址，可以改变默认的出口IP，每次调用会在数组中随机选择一个。考虑到可能会有需要排除的IP，所以这里不自动配置为所有的IP。
 * eg: $curl_ip_config = array('11.11.11.11', '22.22.22.22');
 */
$local_ip_config = array();

/**
 * cookie目录
 */
$cookie_dir = file_exists('/dev/shm/') ? '/dev/shm/' : '/tmp/';

/**
 * 临时文件目录
 */
$tmpfile_dir = file_exists('/dev/shm/') ? '/dev/shm/' : '/tmp/';

/**
 * 清除过期的cookie文件和下载临时文件
 */
clear_curl_file();

/**
 * GET方式抓取网页
 *
 * @param string $url    网页URL地址
 * @param string $encode 返回的页面编码，默认为GBK，设置为空值则不转换
 * @return string        网页HTML内容
 */
function curl_get($url, $encode = 'utf-8') {
    return curl_func($url, 'GET', null, null, null, $encode);
}

/**
 * POST方式请求网页
 *
 * @param string $url    请求的URL地址
 * @param array  $data   发送的POST数据
 * @param string $encode 返回的页面编码，默认为GBK，设置为空值则不转换
 * @return bool
 */
function curl_post($url, $data, $encode = 'utf-8') {
    return curl_func($url, 'POST', $data, null, null, $encode);
}

/**
 * 获取页面的HEADER信息
 * HTTP状态码并不是以“名称:值”的形式返回，这里以http_code作为它的名称，其他的值都有固定的名称，并且转成小写
 *
 * @param string $url URL地址
 * @return array      返回HEADER数组
 */
function curl_header($url, $follow = true) {
    $header_text = curl_func($url, 'HEADER');

    if (!$header_text) {
        /**
         * 获取HTTP头失败
         */
        return false;
    }
    $header_array = explode("\r\n\r\n", trim($header_text));
    if ($follow) {
        $last_header = array_pop($header_array);
    } else {
        $last_header = array_shift($header_array);
    }

    $lines = explode("\n", trim($last_header));

    /**
     * 处理状态码
     */
    $status_line = trim(array_shift($lines));
    preg_match("/(\d\d\d)/", $status_line, $preg);
    $header['http_code'] = $preg[1];
    foreach ($lines as $line) {
        list($key, $val) = explode(':', $line, 2);
        $key          = str_replace('-', '_', strtolower(trim($key)));
        $header[$key] = trim($val);
    }

    return $header;
}

/**
 * 下载文件
 *
 * @param $url  string 文件地址
 * @param $path string 保存到的本地路径
 * @return bool string 下载是否成功
 */
function curl_down($url, $path, $data = null, $proxy = null) {
    if (empty($data)) {
        $method = 'GET';
    } else {
        $method = 'POST';
    }

    return curl_func($url, $method, $data, $path, $proxy);
}

/**
 * 使用代理发起GET请求
 *
 * @param string $url    请求的URL地址
 * @param string $proxy  代理地址
 * @param string $encode 返回编码
 * @return string           网页内容
 */
function curl_get_by_proxy($url, $proxy, $encode = 'utf-8') {
    return curl_func($url, 'GET', null, null, $proxy, $encode);
}

/**
 * 使用代理发起POST请求
 *
 * @param string $url    请求的URL地址
 * @param string $proxy  代理地址
 * @param string $encode 返回编码
 * @return string           网页内容
 */
function curl_post_by_proxy($url, $data, $proxy, $encode = 'gbk') {
    return curl_func($url, 'POST', $data, null, $proxy, $encode);
}

/**
 * @param $url
 * @param $path_pre
 * @return bool|null|string
 */
function img_down($url, $path_pre) {
    $img_tmp = '/tmp/curl_imgtmp_pid_' . getmypid();
    $res     = curl_down($url, $img_tmp);
    if (empty($res)) {
        return $res;
    }
    $ext = get_img_ext($img_tmp);
    if (empty($ext)) {
        return null;
    }
    $path = "{$path_pre}.{$ext}";
    @mkdir(dirname($path), 0777, true);

    /**
     * 转移临时的文件路径
     */
    rename($img_tmp, $path);

    return $path;
}

/**
 * @param $path
 * @return bool|string
 */
function get_img_ext($path) {
    $types = array(
        1 => 'gif',
        2 => 'jpg',
        3 => 'png',
        6 => 'bmp',
    );
    $info  = @getimagesize($path);
    if (isset($types[$info[2]])) {
        $ext = $info['type'] = $types[$info[2]];
        $ext == 'jpeg' && $ext = 'jpg';
    } else {
        $ext = false;
    }

    return $ext;
}

/**
 * 返回文件的大小，用于下载文件后判断与本地文件大小是否相同
 * curl_getinfo()方式获得的size_download并不一定是文件的真实大小
 *
 * @param  string $url URL地址
 * @return string         网络文件的大小
 */
function get_file_size($url) {
    $header = curl_header($url);
    if (!empty($header['content_length'])) {
        return $header['content_length'];
    } else {
        return false;
    }
}

/**
 * 获取状态码
 *
 * @param  string $url URL地址
 * @return string      状态码
 */
function get_http_code($url, $follow = true) {
    $header = curl_header($url, $follow);
    if (!empty($header['http_code'])) {
        return $header['http_code'];
    } else {
        return false;
    }
}

/**
 * 获取URL文件后缀
 *
 * @param  string $url URL地址
 * @return array      文件类型的后缀
 */
function curl_get_ext($url) {
    $header = curl_header($url);
    if (!empty($header['content_type'])) {
        @list($type, $ext) = @explode('/', $header['content_type']);
        if (!empty($type) && !empty($ext)) {
            return array($type, $ext);
        } else {
            return array('', '');
        }
    } else {
        return array('', '');
    }
}

/**
 * 封装curl操作
 * 待改进，下载到临时文件，下载成功后再转移（已经有文件则覆盖），下载失败则删除。
 * 待改进，参数形式改成curl_func($url, $method, $data=null, savepath=null, $proxy=null, $return_encode='gbk')
 *
 * @param string $url           请求的URL地址
 * @param string $method        请求的方法(POST, GET, HEADER, DOWN)
 * @param array  $arg           POST方式为POST数据，DOWN方式时为下载保存的路径
 * @param string $return_encode 网页返回的编码
 * @param string $proxy         代理
 * @return array                 返回内容。4xx序列错误和空白页面会返回NULL，curl抓取错误返回False。结果正常则返回页面内容。
 */
function curl_func($url, $method, $data = null, $savepath = null, $proxy = null, $return_encode = null) {
    $tmpfile   = '';
    $error_msg = '';

    global $colors, $cookie_dir, $tmpfile_dir, $referer_config, $useragent_config, $local_ip_config;
    global $retry_config, $connect_timeout_config, $fetch_timeout_config, $download_timeout_config;

    // 控制台输出颜色
    extract($colors);

    // 重试和超时
    empty($retry_config) && $retry_config = 3;
    empty($timeout_config) && $timeout_config = 5;

    // 去除URL中的/../
    $url = get_absolute_path($url);

    // 去除实体转码
    $url = htmlspecialchars_decode($url);

    // 统计数据
    if (function_exists('mp_counter')) {
        if (!empty($savepath)) {
            mp_counter('down_total');   // 下载次数计数
        } else {
            if ($method == 'HEADER') {
                mp_counter('header_total'); // 抓取HTTP头次数计数
            } else {
                mp_counter('fetch_total');  // 抓取网页次数计数
            }
        }
    }

    for ($i = 0; $i < $retry_config; $i++) {
        // 初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        // 设置超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout_config);      // 连接超时
        if (empty($savepath)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $fetch_timeout_config);           // 抓取网页(包括HEADER)超时
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT, $download_timeout_config);        // 下载文件超时
        }

        // 接收网页内容到变量
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 忽略SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // 设置referer, 默认为空
        foreach ($referer_config as $domain => $referer) {
            if (stripos($url, $domain) !== false) {
                curl_setopt($ch, CURLOPT_REFERER, $referer);
                break;
            }
        }

        // 设置HTTP请求标识，默认为百度蜘蛛
        foreach ($useragent_config as $domain => $ua) {
            if (stripos($url, $domain) !== false) {
                $useragent = $ua;
                break;
            }
        }
        if (empty($useragent)) {
            $useragent = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

        // 出口IP
        if (!empty($local_ip_config)) {
            curl_setopt($ch, CURLOPT_INTERFACE, $local_ip_config[array_rand($local_ip_config)]);
        }

        // 设置代理
        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }

        // 设置允许接收gzip压缩数据，以及解压，抓取HEADER时不使用(获取不到正确的文件大小，影响判断下载成功)
        if ($method != 'HEADER') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip, deflate'));
            curl_setopt($ch, CURLOPT_ENCODING, "");
        }

        // 遇到301和302转向自动跳转继续抓取，如果用于WEB程序并且设置了open_basedir，这个选项无效
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // 最大转向次数，避免进入到死循环
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        // 启用cookie
        $cookie_path = $cookie_dir . 'curl_cookie_pid_' . get_ppid();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);

        // 设置post参数内容
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // 设置用于下载的参数
        if (!empty($savepath)) {
            $tmpfile = $tmpfile_dir . '/curl_tmpfile_pid_' . getmypid();
            file_exists($tmpfile) && unlink($tmpfile);
            $fp = fopen($tmpfile, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
        }

        // 仅获取header
        if ($method == 'HEADER') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        // 抓取结果
        $curl_res = curl_exec($ch);
        // curl info
        $info = curl_getinfo($ch);

        // 错误信息
        $error_msg = curl_error($ch);
        $error_no  = curl_errno($ch);

        // 关闭CURL句柄
        curl_close($ch);

        // 如果CURL有错误信息则判断为抓取失败，重试
        if (!empty($error_no) || !empty($error_msg)) {
            $error_msg = "{$error_msg}($error_no)";
            curl_msg($error_msg, $method, $url, 'yellow');
            continue;
        }

        // 统计流量
        if (function_exists('mp_counter')) {
            if (!empty($info['size_download']) && $info['size_download'] > 0) {
                mp_counter('download_total', $info['size_download']);
            }
        }

        // 对结果进行处理
        if ($method == 'HEADER') {
            // 返回header信息
            return $curl_res;
        } else {
            // 最终的状态码
            $status_code = $info['http_code'];

            if (in_array($status_code, array_merge(range(400, 417), array(500, 444)))) {
                // 非服务器故障性的错误，直接退出，返回NULL
                $error_msg = $status_code;
                if (!empty($savepath)) {
                    $method = "{$method}|DOWN";
                }
                curl_msg($error_msg, $method, $url, 'red');

                return null;
            }
            if ($status_code != 200) {
                // 防止网站502等临时错误，排除了上面的情况后，非200就重试。这一条规则需要后续根据情况来改进。
                // curl执行过程中会自动跳转，这里不会出现301和302，除非跳转次数超过CURLOPT_MAXREDIRS的值
                $error_msg = $status_code;
                curl_msg($error_msg, $method, $url, 'yellow');
                continue;
            }

            if (empty($savepath)) {
                // 抓取页面
                if (empty($curl_res)) {
                    // 返回NULL值，调用处注意判断
                    return null;
                } else {
                    // 默认将页面以GBK编码返回

                    // 分析页面编码
                    preg_match_all("/<meta.*?charset=(\"|'?)(.*?)(;|\"|'|\s)/is", $curl_res, $matches);

                    // 转码条件：1)匹配到编码, 2)返回编码不为空, 3）匹配到的编码和返回编码不相同
                    if (!empty($matches[2][0]) && !empty($return_encode)
                        && str_replace('-', '', strtolower($matches[2][0]))
                           != str_replace('-', '', strtolower($return_encode))
                    ) {
                        $curl_res = @iconv($matches[2][0], "{$return_encode}//IGNORE", $curl_res);
                        // 替换网页标明的编码
                        $curl_res = str_ireplace($matches[2][0], $return_encode, $curl_res);
                    }

                    // iconv如果失败则返回空白页
                    if (empty($curl_res)) {
                        return null;
                    } else {
                        // 将相对路径转换为绝对路径
                        $curl_res = relative_to_absolute($curl_res, $url);

                        return $curl_res;
                    }
                }
            } else {
                // 下载文件
                if (@filesize($tmpfile) == 0) {
                    $error_msg = 'Emtpy Content';
                    continue;
                }

                // 统计下载文件量
                if (function_exists('mp_counter')) {
                    mp_counter('download_size', filesize($tmpfile));
                }
                // 创建目录
                @mkdir(dirname($savepath), 0777, true);
                // 转移临时的文件路径
                rename($tmpfile, $savepath);

                return true;
            }
        }
    }

    // 如果是下载或者抓取header，并且错误代码为6(域名无法解析)，则不输出错误。失效的图片引用太多了。
    // 域名不合法的时候也无法输出错误了，需要改进，在前面判断URL的合法性
    if (!(($method == 'HEADER' || !empty($savepath)) && !empty($error_no) && $error_no == 6)) {
        if (!empty($savepath)) {
            $method = "{$method}|DOWN";
        }
        curl_msg($error_msg, $method, $url, 'red');
    }

    // 统计数据
    if (function_exists('mp_counter')) {
        if (!empty($savepath)) {
            mp_counter('down_failed');
        } elseif ($method == 'HEADER') {
            mp_counter('header_failed');
        } else {
            mp_counter('fetch_failed');
        }
    }

    return false;
}

/**
 * 输出错误信息
 *
 * @param string $msg    错误信息
 * @param string $method 请求方式
 * @param string $url    URL地址
 * @param string $color  颜色
 */
function curl_msg($msg, $method, $url, $color) {
    global $colors;
    extract($colors);

    // 多并发下建议关闭黄色错误输出
    //$available_msg[] = 'yellow';
    $available_msg[] = 'red';

    if (php_sapi_name() != 'cli') {
        return;
    }

    if (!in_array($color, $available_msg)) {
        return;
    }

    echo "{$color['reverse']}" . $colors[$color] . "({$method})[cURL ERROR: {$msg}] {$url}{$color['end']}\n";
}

/**
 * 将URL地址转换为绝对路径
 * URL地址有可能会遇到包含'/../'构成的相对路径，curl不会自动转换
 * echo get_absolute_path("http://www.a.com/a/../b/../c/../././index.php");
 * 结果为：http://www.a.com/index.php
 *
 * @param  string $path 需要处理的URL
 * @return string       返回URL的绝对路径
 */
function get_absolute_path($path) {
    $parts     = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) {
            continue;
        }
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }

    return str_replace(':/', '://', implode('/', $absolutes));
}

/**
 * 使用图片URL的md5值作为路径，并且分级目录
 * 深度为e时，伪静态规则为rewrite ^/(.)(.)(.)(.*)$ /$1/$2/$3/$4 break;
 * 平均1篇文章1张图片，三千万文章，三千万图片，3级目录最终4096子目录，平均每目录7324个图片
 *
 * @param string $str  原图片地址
 * @param int    $deep 目录深度
 * @return string     返回分级目录
 */
function md5_path($str, $deep = 3) {
    $md5 = substr(md5($str), 0, 16);
    preg_match_all('/./', $md5, $preg);
    $res = '';
    for ($i = 0; $i < count($preg[0]); $i++) {
        $res .= $preg[0][$i];
        if ($i < $deep) {
            $res .= '/';
        }
    }

    return $res;
}

function relative_to_absolute($content, $url) {
    $content = preg_replace("/src\s*=\s*\"\s*/", 'src="', $content);
    $content = preg_replace("/href\s*=\s*\"\s*/", 'href="', $content);

    preg_match("/(http|https|ftp):\/\/[^\/]*/", $url, $preg_base);
    if (!empty($preg_base[0])) {
        // $preg_base[0]内容如http://www.yundaiwei.com
        // 这里处理掉以/开头的链接，也就是相对于网站根目录的路径
        $content = preg_replace('/href=\s*"\//i', 'href="' . $preg_base[0] . '/', $content);
        $content = preg_replace('/src=\s*"\//ims', 'src="' . $preg_base[0] . '/', $content);
    }

    preg_match("/(http|https|ftp):\/\/.*\//", $url, $preg_full);
    if (!empty($preg_full[0])) {
        // 这里处理掉相对于目录的路径，如src="../../images/jobs/lippman.gif"
        // 排除掉file://开头的本地文件链接，排除掉data:image方式的BASE64图片
        $content = preg_replace('/href=\s*"\s*(?!http|file:\/\/|data:image|javascript)/i', 'href="' . $preg_full[0], $content);
        $content = preg_replace('/src=\s*"\s*(?!http|file:\/\/|data:image|javascript)/i', 'src="' . $preg_full[0], $content);
    }

    return $content;
}

/**
 * 清除过期的cookie文件和下载临时文件
 */
function clear_curl_file() {
    global $cookie_dir;

    $cookie_files = glob("{$cookie_dir}curl_*_pid_*");
    $tmp_files    = glob("/tmp/curl_*_pid_*");
    $files        = array_merge($cookie_files, $tmp_files);

    foreach ($files as $file) {
        preg_match("/pid_(\d*)/", $file, $preg);
        $pid      = $preg[1];
        $exe_path = "/proc/{$pid}/exe";
        // 如果文件不存在则说明进程不存在，判断是否为PHP进程，排除php-fpm进程
        if (!file_exists($exe_path)
            || stripos(readlink($exe_path), 'php') === false
            || stripos(readlink($exe_path), 'php-fpm') === true
        ) {
            sem_remove(sem_get(ftok($file, 'a')));
            unlink($file);
        }
    }
}


/**
 * 如果是在子进程中，获取父进程PID，否则获取自身PID
 *
 * @return int
 */
if (!function_exists('get_ppid')) {
    function get_ppid() {
        /**
         * 这里需要识别出是在子进程中调用还是在父进程中调用，不同的形式，保存的变量内容的文件位置需要保持一致
         */
        $ppid = posix_getppid();

        /**
         * 理论上，这种判断方式可能会出坑。但在实际使用中，除了fork出的子进程外，不太可能让PHP进程的父进程的程序名中出现php字样
         */
        if (strpos(@readlink("/proc/{$ppid}/exe"), 'php') === false) {
            $pid = getmypid();
        } else {
            $pid = $ppid;
        }

        return $pid;
    }
}

// UTF-8转GBK
if (!function_exists('u2g')) {
    function u2g($string) {
        return @iconv("UTF-8", "GBK//IGNORE", $string);
    }
}

// GBK转UTF-8
if (!function_exists('g2u')) {
    function g2u($string) {
        return @iconv("GBK", "UTF-8//IGNORE", $string);
    }
}