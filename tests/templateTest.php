<?php

require __DIR__ . '/public.php';

global $php;
$template = new \Zoco\Template();

$template->assign_by_ref('php_genv', $php->genv);
$template->assign_by_ref('php', $php->env);
$template->template_dir = Zoco::$appPath . '/templates';
$template->assign('my_var', 'zoco use smarty');
$template->display('tpl_test.html');