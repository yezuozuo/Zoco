<?php

require __DIR__ . '/public.php';

$db = new \Zoco\ZDB();

//$db->open('dbtest');

$value = $db->fetch('key666');
echo $value . "<br>";

//$db->delete('key666');
//$value = $db->fetch('key666');
//echo $value."<br>";

//$db->insert("key666","value666");
//$value = $db->fetch('key666');
//echo $value."<br>";
// for($i = 0;$i < 10000;$i++)
// {
// 	$db->delete("key".$i);
// }
// for($i = 0;$i < 10000;$i++)
// {
// 	$db->insert("key".$i,"value".$i);
// }

$db->close();