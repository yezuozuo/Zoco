<?php

require __DIR__ . '/public.php';

$config = array(
    'loginTable' => 'login_table',
);
$auth   = new \Zoco\Auth($config);

//var_dump($_SESSION);

//$auth->createTable();

$auth->register('jiaxiaoqi', '123');
echo $auth->login('wangzhihao', '123', false);

//$auth::loginRequire();

//$auth->logout();
//
//var_dump($auth->isLogin);

//$auth->changePassword('1','123','1233');