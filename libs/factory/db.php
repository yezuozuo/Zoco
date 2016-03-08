<?php

global $php;

if (empty($php->config['db'][$php->factoryKey])) {
    throw new Exception("db->{$php->factoryKey} is not found.");
}
$db = new Zoco\Database($php->config['db'][$php->factoryKey]);
$db->connect();

return $db;