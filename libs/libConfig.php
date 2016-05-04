<?php

date_default_timezone_set('Asia/Shanghai');

set_error_handler('zoco_error_log', E_ALL & ~E_DEPRECATED & ~E_STRICT);
$GLOBALS['uuid'] = uniqid('', true);
function zoco_error_log($errorno, $errorstr, $errorfile, $errorline) {
    $curerrorno = error_reporting();
    if (($curerrorno & ~$errorno) == $curerrorno) {
        return true;
    }
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $EXIT        = false;
    switch ($errorno) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error_type = 'Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error_type = 'Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error_type = 'Fatal Error';
            $EXIT       = true;
            break;
        default:
            $error_type = 'Fatal Error';
            $EXIT       = true;
            break;
    }
    $timezone         = date_default_timezone_get();
    $request_uri_text = $request_uri ? '   [REQUEST_URI:' . $request_uri . ']' : '   [REQUEST_URI: Unkown]';
    $text             = '[' . date('d-M-Y H:i:s', time()) . ' ' . $timezone . '] ' . $GLOBALS['uuid'] . ' PHP' . ' ' . $error_type . ':  ' . $errorstr . ' in ' . $errorfile . ' on line ' . $errorline . $request_uri_text . "\n";
    $log_path         = __DIR__.'/../log/php-fpm.log';
    if (is_writeable($log_path)) {
        file_put_contents($log_path, $text, FILE_APPEND);
    }
    if ($EXIT) {
        die();
    }
    return true;
}

/**
 * libs的目录
 */
if (!defined('LIBPATH')) {
    define('LIBPATH', __DIR__ . '/');
}

/**
 * 定义换行
 */
if (!defined('NL')) {
    if (PHP_OS == 'WINNT') {
        define('NL', "\r\n");
    } else {
        define('NL', "\n");
    }
}

/**
 * 浏览器下的换行
 */
if (!defined('BL')) {
    define('BL', "<br/>");
}

require_once LIBPATH . '/Zoco/Zoco.php';
require_once LIBPATH . '/Zoco/Loader.php';

/**
 * 注册顶层命名空间到自动载入器
 */
Zoco\Loader::addNameSpace('Zoco', __DIR__ . '/Zoco');
spl_autoload_register('\\Zoco\\Loader::autoload');

global $php;
$php = Zoco::getInstance();

/**
 * 传入一个数据库表，返回一个封装此表的Model接口
 *
 * @param $tableName
 * @param $dbKey
 * @return Zoco\Model
 */
function table($tableName, $dbKey = 'master') {
    return Zoco::getInstance()->model->loadTable($tableName, $dbKey);
}

/**
 * 生产一个model接口，模型在注册树上为单例
 *
 * @param $model_name
 * @param $db_key
 * @return \Zoco\Model
 */
function model($modelName, $dbKey = 'master') {
    return Zoco::getInstance()->model->loadModel($modelName, $dbKey);
}