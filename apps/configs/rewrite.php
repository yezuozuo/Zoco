<?php

$rewrite[] = array(
    'regx' => '^/([a-z]+)/(\d+)\.html$',
    'mvc'  => array(
        'controller' => 'page',
        'view'       => 'detail',
    ),
    'get'  => 'app,id',
);

return $rewrite;