<?php

require __DIR__ . '/../public.php';

global $php;

$config = array('name' => 'queue_file');
$type   = '\Zoco\Queue\Redis';
$queue  = new \Zoco\Queue($php->config['redis']['master'], $type);

$queue->push('b');

//echo $queue->pop();

var_dump($queue->getMulti(5));