<?php

require __DIR__ . '/public.php';

$ip = '113.123.22.2222';
$res = \Zoco\Validate::ip($ip);
var_dump($res);

$ip  = '113.123.22.222';
$res = \Zoco\Validate::ip($ip);
var_dump($res);


$ascii = '122';
$res   = \Zoco\Validate::ascii($ascii);
var_dump($res);

$englishWord = 'abc';
$res = \Zoco\Validate::word($englishWord);
var_dump($res);

$englishWord = '网  等的';
$res = \Zoco\Validate::realString($englishWord);
var_dump($res);

$res = \Zoco\Validate::check('email', '10@qq.com');
var_dump($res);