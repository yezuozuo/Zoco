<?php

require __DIR__ . '/public.php';

global $php;

$result = $php->redis->keys('*');
var_dump($result);
\Zoco\Redis::getIncreaseId('a');