<?php

require __DIR__ . '/public.php';

$array = array('a', 'f', 'c', 'b', 'e', 'h', 'j', 'i', 'g');

//var_dump(\Zoco\Sort::bubbleSort($array));
//var_dump(\Zoco\Sort::bubbleSort($array,'asc'));
//var_dump(\Zoco\Sort::quickSort($array));
//var_dump(\Zoco\Sort::quickSort($array,'asc'));
//var_dump(\Zoco\Sort::selectSort($array));
//var_dump(\Zoco\Sort::selectSort($array,'asc'));
//var_dump(\Zoco\Sort::insertSort($array));
//var_dump(\Zoco\Sort::insertSort($array,'asc'));

// find
$arr = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
var_dump(\Zoco\Sort::halfSearch($arr, 8));