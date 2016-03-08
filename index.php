<?php

if (!defined('DEBUG')) {
    define('DEBUG', 'on');
}

/**
 * PHP程序的根目录
 */
if (!defined('WEBPATH')) {
    define('WEBPATH', __DIR__);
}

/**
 * 框架入口文件
 */
require __DIR__ . '/libs/libConfig.php';

/**
 * 启动
 */
global $php;
$php->runMVC();