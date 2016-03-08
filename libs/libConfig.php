<?php

date_default_timezone_set('Asia/Shanghai');

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