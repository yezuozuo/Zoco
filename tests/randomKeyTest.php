<?php

require __DIR__ . '/public.php';

echo \Zoco\RandomKey::getChineseCharacter() . BL;

echo \Zoco\RandomKey::produceString() . BL;

echo \Zoco\RandomKey::randTime() . BL;

echo \Zoco\RandomKey::randMd5();