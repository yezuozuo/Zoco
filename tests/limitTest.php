<?php

require __DIR__ . '/public.php';

global $php;

$php->limit->addCount('t');

var_dump($php->limit->exceed('t', 5));