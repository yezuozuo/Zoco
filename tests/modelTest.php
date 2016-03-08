<?php

require __DIR__ . '/public.php';

global $php;

$model = new \Zoco\Model($php);

$model->table = 'api_analyze_base';

$model->select = '*';

//$params = array('order' => 'time','page' => true);
//
//$res = $model->gets($params);
//
//var_dump($res);

$res = $model->desc();
var_dump($res);