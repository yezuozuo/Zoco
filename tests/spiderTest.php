<?php

require __DIR__ . '/public.php';

$spider = new \Zoco\Spider('http://www.yundaiwei.com');

$spider->run();