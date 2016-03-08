<?php

$log['db'] = array(
    'type' => 'DBLog',
);

$log['echo'] = array(
    'type' => 'EchoLog',
    'display' => false,
);

$log['php'] = array(
    'type' => 'PHPLog',
);

/**
 * date 和 file不能同时存在
 */
$log['master'] = array(
    'type' => 'FileLog',
    /**
     * 文件名
     */
    //'file' => 'fileLog.log',

    /**
     * 目录
     */
    'dir'  => WEBPATH . '/data/logs/',
    /**
     * 开启日期
     */
    'date' => true,
);

return $log;