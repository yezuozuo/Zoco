<?php

require __DIR__ . '/public.php';

$arr = array(
    '1' => 'a',
    '2' => 'b',
);
echo \Zoco\Form::select('select', $arr) . BL;

echo \Zoco\Form::radio('radio', $arr) . BL;

echo \Zoco\Form::checkbox('checkbox', $arr) . BL;

//echo \Zoco\Form::upload('test','file://localhost#sess/').BL;

echo \Zoco\Form::upload('test') . BL;

echo \Zoco\Form::input('input', 'input') . BL;

echo \Zoco\Form::button('button', 'test') . BL;

echo \Zoco\Form::password('password', 'password') . BL;

echo \Zoco\Form::text('text', 'text'), BL;