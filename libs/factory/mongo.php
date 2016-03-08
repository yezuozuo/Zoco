<?php

global $php;

if ($php->factoryKey == 'master') {
    $config = $php->config['mongo']['master'];
    if (empty($config['host'])) {
        $config['host'] = '127.0.0.1';
    }
} else {
    $config = $php->config['mongo'][$php->factoryKey];
    if (empty($config) || empty($config['host'])) {
        throw new Exception("mongodb require server host ip.");
    }
}

if (empty($config['port'])) {
    $config['port'] = '27017';
}
if (!isset($config['option'])) {
    $config['option'] = array();
}

$url = "mongodb://{$config['host']}:{$config['port']}";

$mongo = new MongoClient($url, $config['option']);

return $mongo;