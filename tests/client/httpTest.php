<?php

require __DIR__ . '/../public.php';

$http = new \Zoco\Client\Http('127.0.0.1', '80');

//\Zoco\Client\Http::quickGet('https://www.baidu.com');

\Zoco\Client\Http::quickPost('http://www.baidu.com', '1');