<?php
/**
 * @since  2016-01-26
 */

require __DIR__ . '/public.php';

$res = \Zoco\RandomKey::zocoRandom(1, 33, 6);

$tmp   = \Zoco\RandomKey::zocoRandom(1, 16, 1);
$res[] = $tmp[0];

foreach ($res as $val) {
    echo $val . "   ";
}