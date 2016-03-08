<?php

require __DIR__ . '/public.php';

$arr = array(
    '1' => 'a',
    '2' => 'b',
    '3' => 'c',
    '4' => 'd',
);

$arr = new \Zoco\ArrayObject($arr);

echo $arr->current() . NL;

echo $arr->key() . NL;

echo $arr->valid() . NL;

echo $arr->rewind() . NL;

echo $arr->serialize() . NL;

echo $arr->offsetGet('2') . NL;

$arr->offsetSet('2', 'g');

echo $arr->offsetGet('2') . NL;

echo $arr->join('a') . NL;

$arr->insert(1, '_');

echo $arr->offsetGet(1) . NL;

var_dump($arr->toArray());