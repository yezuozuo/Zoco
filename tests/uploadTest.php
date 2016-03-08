<?php

require __DIR__ . '/public.php';

global $php;

$config = $php->config['upload'];

$upload = new \Zoco\Upload($config);

//echo $upload->getMimeType('image/jpeg');

//echo \Zoco\Upload::getFileExt('test.php').BL;

\Zoco\Upload::moveUploadFile(__DIR__ . '/test.php', __DIR__ . '/newTest.php');