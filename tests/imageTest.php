<?php

require __DIR__ . '/public.php';

global $php;

$image = new \Zoco\Image();

$source = '/Users//Downloads/test.jpg';
$dest   = '/Users//Downloads/a.jpg';

//\Zoco\Image::cut($source,$dest,300,4000);

//\Zoco\Image::thumbnail($source,$dest,100);

//var_dump(\Zoco\Image::readFile($source));

//not done
//\Zoco\Image::waterMark($source);

//$php->http->header('Content-Type','image/jpeg');
//\Zoco\Image::verifyCodeGD();

//not done
//$php->http->header('Content-Type','image/png');
//\Zoco\Image::verifyCodeIm();

//not done
//$php->http->header('Content-Type','image/png');
//\Zoco\Image::verifyTTF(__DIR__.'/apps/static/font/Jura.ttf');

//echo \Zoco\Image::thumbName($dest);