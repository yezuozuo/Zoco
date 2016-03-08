<?php

require __DIR__ . '/../public.php';

$config = array('name' => 'queue_file');
$type   = '\Zoco\Queue\File';
$queue  = new \Zoco\Queue($config, $type);

//$queue->push('b');

echo $queue->pop();
