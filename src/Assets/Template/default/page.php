<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>
    <link href="/<?= $this->e($template) ?>/css/app.min.css" rel="stylesheet">
    <?= $this->section('css') ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <header class="row">
        <div class="col-xs-12 titlebar">
            <?= $this->section('header') ?>
        </div>
    </header>
    <?= $this->section('menu') ?>
    <main class="row row-border">
        <div class="col-xs-12">
            <div class="content-wrapper">
                <?= $this->section('main') ?>
                <?php if (!$this->uri('/')): ?>
                    <a href="/" class="btn btn-default btn-block" style="
                           margin-top: 8px;
                           /*background-color: #fff;*/
                           /*color: #3E3F3A;*/
                           /*border: 1px solid #E4DDD3;*/
                           /*font-size: 16px;*/
                           ">
                        <span class="icon-home"></span> На главную
                    </a>
                    <!--<div class="separator"></div>-->
                    <!--<div class="home"><a href="/"><span class="icon-home"></span></a></div>-->
                    <!--<div class="separator"></div>-->
                <?php endif ?>
            </div>
        </div>
    </main>
    <footer class="row">
        <div class="col-xs-12 footbar">
            <div class="side text-left">&copy;MBrowser <?= date('Y') ?></div>

            <div class="side text-right"><span class="icon icon-user"></span><?= $this->online() ?></div>
            <?= $this->section('footer') ?>
        </div>
    </footer>
</div>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
<?= $this->section('js') ?>
</body>
</html>