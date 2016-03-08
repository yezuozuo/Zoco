<?php

require __DIR__ . '/public.php';

$key = \Zoco\RandomKey::randMd5(32);

$des = new \Zoco\DES($key);