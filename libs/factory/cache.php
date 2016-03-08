<?php

global $php;

if ($php->factoryKey == 'master' && empty($php->config['cache']['master'])) {
    $php->config['cache']['master'] = array(
        'type'     => 'FileCache',
        'cacheDir' => WEBPATH . '/data/cache/fileCache',
    );
}

return \Zoco\Factory::getCache($php->factoryKey);