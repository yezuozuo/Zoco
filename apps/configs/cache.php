<?php

$cache['session'] = array(
    'type'     => 'FileCache',
    'cacheDir' => WEBPATH . '/data/cache/fileCache/session',
);

$cache['master'] = array(
    //'type' => 'DbCache',
    //'type' => 'FileCache',
    'type'     => 'Redis',
    'cacheDir' => WEBPATH . '/data/cache/fileCache/',
);

return $cache;