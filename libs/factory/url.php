<?php

global $php;

$config = $php->config['url'][$php->factoryKey];

return new \Zoco\URL($config);