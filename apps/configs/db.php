<?php

$db['master'] = array(
    'type'       => Zoco\Database::TYPE_MYSQL,
    'host'       => "127.0.0.1",
    'port'       => 3306,
    'user'       => "root",
    'password'   => "",
    'name'       => "zoco",
    'charset'    => "utf8",
    'persistent' => false,
);

$db['database'] = array(
    'type'       => Zoco\Database::TYPE_MYSQL,
    'host'       => "127.0.0.1",
    'port'       => 3306,
    'user'       => "root",
    'password'   => "",
    'name'       => "information_schema",
    'charset'    => "utf8",
    'persistent' => false,
);

return $db;