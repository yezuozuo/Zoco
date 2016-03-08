<?php

require __DIR__ . '/public.php';

$zoco = Zoco::getInstance();

$zoco->__init();

$view = new \Zoco\View($zoco);

$view->assign('test', 'zoco');

echo $view->get('test') . BL;

$view->trace('a', '1');

$view->fetch('index.php', true);

$view->showTrace(true);

$view->showTrace();