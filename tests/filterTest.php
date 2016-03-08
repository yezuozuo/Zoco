<?php

require __DIR__ . '/public.php';

$filter = new \Zoco\Filter();

$str = 'a\\\\/d/ad132131/dadada090-80\d';
\Zoco\Filter::safe($str);

var_dump($str);