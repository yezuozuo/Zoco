<?php

require __DIR__ . '/../public.php';

$curl = new \Zoco\Client\CURL(true);

$curl->init();

$curl->includeResponseHeaders(false);
$res = $curl->get('https://www.baidu.com');

var_dump($res);

$fp = fopen('test.log', 'w');

$curl->includeResponseHeaders(false);
$res = $curl->download('http://www.baidu.com', $fp, '127.0.0.1');

var_dump($res);

$curl->close();
fclose($fp);