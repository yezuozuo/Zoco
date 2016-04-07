<?php

require __DIR__ . '/public.php';

//\Zoco\Email::send('test','justyehao@qq.com',"Subject: Foo\nFrom: Rizzlas@my.domain\n");

$mailto  = 'justyehao@qq.com';
$subject = '你个小蹦但';

$from_name = 'test@iloveyou.com';
$from_mail = 'zoco';
$replyto   = 'wang.zhihao@test.com';
$message   = '笑崩但';
$content   = '小懒猪';

$header = "From: " . $from_name . " <" . $from_mail . ">\n";
$header .= "Reply-To: " . $replyto . "\n";
$header .= "MIME-Version: 1.0\n";

$emessage = $message . "\n\n";
mail($mailto, $subject, $emessage, $header);