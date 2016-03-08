<?php

$tpl = new Zoco\Template();
global $php;
$tpl->assign_by_ref('php_genv', $php->genv);
$tpl->assign_by_ref('php', $php->env);
if (defined('TPL_DIR')) {
    $tpl->template_dir = TPL_DIR;
} else {
    if (is_dir(Zoco::$appPath . '/templates')) {
        $tpl->template_dir = Zoco::$appPath . '/templates';
    } else {
        $tpl->template_dir = WEBPATH . '/templates';
    }
}
define('TPL_BASE', $tpl->template_dir);
if (DEBUG == 'on') {
    $tpl->compile_check = true;
} else {
    $tpl->compile_check = false;
}

return $tpl;