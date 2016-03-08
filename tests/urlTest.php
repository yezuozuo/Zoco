<?php

require __DIR__ . '/public.php';

global $php;

$config = $php->config['url'];
$url    = new \Zoco\URL($config);

echo $url->get();

$arr = array();

$url->post($arr);