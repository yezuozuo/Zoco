<?php

require __DIR__ . '/public.php';

$excel   = new \Zoco\Excel();
$content = array(
    array(
        'order_sn'    => 1,
        'add_time'    => 2,
        'region_name' => 3,
        'city'        => 4,
        'company'     => 5,
        'consignee'   => 6,
        'mobile'      => 7,
        'address'     => 8,
        'brand_name'  => 9,
    ),
);
$excel->setContent($content);

$excel->run();