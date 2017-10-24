<?php

require __DIR__ . '/public.php';

global $php;
header("Content-type: text/html; charset=utf-8");
$parser = new \Zoco\Markdown();

$text = 'markdown';
$html = $parser->makeHtml($text);
echo $html;
