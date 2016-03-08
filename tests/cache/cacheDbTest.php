<?php

require __DIR__ . '/../public.php';

/**
 * 需要把/apps/config/cache.php中的配置更改成DbCache
 */
global $php;
$cache = $php->cache;

$cache->setTable('dbCache');
//$cache->createTable();

//$cache->set('a','1');

//var_dump($cache->get('a'));

//$cache->delete('a');

$result = $cache->gets('a');
var_dump($result);