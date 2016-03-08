<?php

global $php;

if (empty($php->config['log'][$php->factoryKey])) {
    throw new \Exception("log->{$php->factoryKey} is not found");
}

$config = $php->config['log'][$php->factoryKey];
if (empty($config['type'])) {
    $config['type'] = 'EchoLog';
}

$class = 'Zoco\\Log\\' . $config['type'];
$log   = new $class($config);

if (!empty($config['level'])) {
    $log->setLevel($config['level']);
}

return $log;