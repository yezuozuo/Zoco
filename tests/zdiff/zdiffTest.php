<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <title>ZDiff</title>
    <link rel="stylesheet" href="styles.css" type="text/css" charset="utf-8"/>
</head>
<body>
<h1>ZDiff</h1>
<hr/>
<?php
require __DIR__ . '/../public.php';
$a = explode("\n", file_get_contents(dirname(__FILE__) . '/a.txt'));
$b = explode("\n", file_get_contents(dirname(__FILE__) . '/b.txt'));

$options = array(
    //'ignoreWhitespace' => true,
    //'ignoreCase' => true,
    'ignoreNewLines' => true,
    //'context'          => 100,
);

$diff = new Zoco\ZDiff($a, $b, $options);

?>
<h2>Side by Side Diff</h2>
<?php
$diff->sideBySide();
?>
<h2>Inline Diff</h2>
<?php
$diff->inline();
?>
<h2>Unified Diff</h2>
<?php
$diff->unified();
?>
<h2>Context Diff</h2>
<?php
$diff->context();
?>
</body>
</html>