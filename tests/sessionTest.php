<?php

require __DIR__ . '/public.php';

global $php;

$cache = $php->config['cache']['session'];

$session = new \Zoco\Session($cache);

$session->start();

var_dump($_SESSION);

$session->set('123');

var_dump($_SESSION);