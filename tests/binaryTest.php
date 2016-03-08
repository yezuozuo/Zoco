<?php

require __DIR__ . '/public.php';

$binary = new \Zoco\Binary();

$binary->addUShort(5);
echo $binary->getUShort() . NL;
echo strlen($binary->body) . NL;

$binary->addUint(6);
echo $binary->getUInt() . NL;
echo strlen($binary->body) . NL;

$binary->addUInt64(622225);
echo $binary->getUInt64() . NL;
echo strlen($binary->body) . NL;

$binary->addInt(213);
echo $binary->getInt() . NL;
echo strlen($binary->body) . NL;

$binary->addTinyString('a');
echo $binary->getString() . NL;
echo strlen($binary->body) . NL;

$binary->addShortString('bbb');
echo $binary->getShortString() . NL;
echo strlen($binary->body) . NL;

$binary->addLongString('dahidhakhdakhd');
echo $binary->getInt32String() . NL;
echo strlen($binary->body) . NL;

$binary->addString('dkahdhla');
echo $binary->getStdString() . NL;
echo strlen($binary->body) . NL;

$binary->addString('dada');
echo $binary->getFixedString(5) . NL;
echo strlen($binary->body) . NL;

$binary->addFloat('1.3131');
echo $binary->getFloat() . NL;
echo strlen($binary->body) . NL;

$binary->addDouble('123.131231312313');
echo $binary->getDouble() . NL;
echo strlen($binary->body) . NL;

$binary->addString('3213');
echo $binary->getUChar() . NL;
echo strlen($binary->body) . NL;