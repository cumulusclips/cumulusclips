<!DOCTYPE html>
<html>
<head>
<title><?=View::$vars->meta->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="CumulusClips" />
<meta name="keywords" content="<?=View::$vars->meta->keywords?>" />
<meta name="description" content="<?=View::$vars->meta->description?>" />
<?php View::WriteMeta(); ?>
<link rel="stylesheet" type="text/css" href="<?=THEME?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?=THEME?>/css/pages.css" />
<?php View::WriteCSS(); ?>

</head>
<body class="<?=Language::GetCSSName()?>">

<!-- BEGIN WRAPPER -->
<div id="wrapper">

    <?php View::Header(); ?>

    <!-- BEGIN MAIN -->
    <div id="main">

        <?php View::Body(); ?>

    </div>
    <!-- END MAIN -->

    <div id="footer-spacer"></div>

</div>
<!-- END WRAPPER -->

<?php View::Footer(); ?>

<script type="text/javascript" src="<?=THEME?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=THEME?>/js/general.js"></script>
<?php View::WriteJs(); ?>

</body>
</html>