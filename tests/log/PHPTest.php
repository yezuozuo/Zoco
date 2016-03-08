<?php

require __DIR__ . '/../public.php';

global $php;

$arr = array(
    'logout' => 'justyehao@qq.com',
    'type'   => 'email',
);

$php->log->__init($arr);
$php->log->put('a');