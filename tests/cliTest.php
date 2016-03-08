<?php

require __DIR__ . '/public.php';
require WEBPATH . '/libs/libConfig.php';
global $php;

require WEBPATH . '/libs/func/cli.php';

//importAllController(WEBPATH.'/apps');
//var_dump($php->env);

//importAllModel(WEBPATH.'/apps');
//var_dump($php->env);


//createControllerClass('test',true);

createModelClass('test', 'a');