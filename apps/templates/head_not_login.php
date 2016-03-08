<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?php echo $title; ?></title>
    <link href="static/css/bootstrap/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="static/css/bootstrap/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
    <link href="static/css/bootstrap/boot-business.css" type="text/css" rel="stylesheet">
    <link href="static/css/base.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="static/js/jquery/jquery-2.0.0.min.js"></script>
    <script type="text/javascript" src="static/js/bootstrap/bootstrap.min.js"></script>
    <script type="text/javascript" src="static/js/bootstrap/bootstrap_custom.js"></script>
    <script type="text/javascript" src="static/js/loading/ajax.js"></script>

<body>
<header id="header">
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a href="index.php" class="brand brand-bootbus"><?php echo $title; ?></a>
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
        </div>
    </div>
</header>
<script>
    $('#logout').click(function () {
        if (!confirm('确定要注销吗?')) {
            return false;
        }
    });
</script>
<div id="loading">
    <ul class="bokeh">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
</div>
<div class="container" style="margin-bottom: 100px;">