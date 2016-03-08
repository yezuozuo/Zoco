<?php

global $php;

$user = new Zoco\Auth($php->config['user']);

return $user;