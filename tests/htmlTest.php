<?php

require __DIR__ . '/public.php';

$content = '<!--test--!><a>aaa</a>';

echo $content . BL;

echo \Zoco\HTML::removeComment($content) . BL;

echo \Zoco\HTML::parseRelativePath('zoco/', 'www.baidu.com') . BL;

echo \Zoco\HTML::removeTag(file_get_contents('https://www.baidu.com'));

