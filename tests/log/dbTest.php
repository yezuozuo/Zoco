<?php

require __DIR__ . '/../public.php';

global $php;

$arr = array(
    'table' => 'dbLog',
    'db'    => $php->db,
);

$php->log->__init($arr);
//$php->log->create();
$php->log->put('a');