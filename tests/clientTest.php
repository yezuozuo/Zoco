<?php

require __DIR__ . '/public.php';

echo \Zoco\Client::requestMethod();

echo \Zoco\Client::getOS();

echo \Zoco\Client::getBrowser();

echo \Zoco\Client::getIP();

//\Zoco\Client::download('text\plain','clientTest.php');