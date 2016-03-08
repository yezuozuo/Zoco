<?php

global $php;

if (!empty($php->config['session']['useZocoSession'])) {
    if (empty($php->config['cache']['session'])) {
        $cache = $php->cache;
    } else {
        $cache = Zoco\Factory::getCache('session');
    }
    $session                = new Zoco\Session($cache);
    $session->usePHPSession = false;
} else {
    $session = new Zoco\Session;
}

return $session;