<?php

require __DIR__ . '/public.php';

$str = 'remote:Total4428(delta0),reused0(delta0),pack-T-reused4428';

$string = new \Zoco\String($str);

echo $string . NL;

echo $string->pos('T') . NL;

echo $string->rpos('t') . NL;

echo $string->ripos('T') . NL;

echo $string->ipos('T') . NL;

echo $string->lower() . NL;

echo $string->upper() . NL;

echo $string->len() . NL;

echo $string->substr(1, 2) . NL;

echo $string->replace('r', 1) . NL;

echo $string->startWith('rem') . NL;
echo $string->startWith('res') . NL;

echo $string->endWith('8') . NL;
echo $string->endWith('7') . NL;

echo $string . NL;
var_dump($string->split('r'));

var_dump($string->toArray(10));

echo $string->versionCompare('1.30.1', '1.3.2') . BL;
echo $string->versionCompare('1.3.1', '1.3.2') . BL;