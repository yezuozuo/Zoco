<?php

require __DIR__ . '/public.php';

\Zoco\Cookie::set('name', 'wangzhihao', 10);
var_dump(\Zoco\Cookie::get('username'));
var_dump($_COOKIE);