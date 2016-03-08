<?php

require __DIR__ . '/public.php';

$obj = new Zoco\CalculateFiles();

/**
 * 所有文件,(默认格式为.php)
 */
$obj->setFileSkip(array());
$obj->setShowFlag(false);
//$obj->run(WEBPATH);
$obj->run(LIBPATH . '/Zoco');

/**git config user.name "Your Name"
 * 如果设置为false,这不ca会显示每个文件的信息，否则显示
 */
//$obj->setShowFlag(false);
//$obj->run('/Users/wangzhihao/workCode/api_project');

/**
 * 会跳过所有All开头的文件
 */
//$obj->setFileSkip(array('All'));
//$obj->run(WEBPATH);


/**
 * 跳过所有I和A开头的文件，（比如接口和抽象类开头）
 */
//$obj->setFileSkip(array('I','A'));
//$obj->run(WEBPATH);
