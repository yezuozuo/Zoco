<?php

if (empty(Zoco::$php->config['upload'])) {
    $config = Zoco::$php->config['upload'];
} else {
    throw new Exception("require upload config");
}

$upload = new \Zoco\Upload($config);

return $upload;