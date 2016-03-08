<?php

require __DIR__ . '/../public.php';

/**
 * 需要把/apps/config/cache.php中的配置更改成DbCache
 */
global $php;
$cache = $php->cache;

//$cache->set('a',1);

//$result = $cache->get('a');
//var_dump($result);

$cache->delete('a');