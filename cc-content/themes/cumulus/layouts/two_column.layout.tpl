<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Cumulus - Free Video Sharing CMS, Free Video Sharing Script, Free Video Sharing Software, YouTube Clone Script</title>
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

        <!-- BEGIN CONTENT -->
        <div id="content">
            <?php View::Body(); ?>
        </div>
        <!-- END CONTENT -->


        <!-- BEGIN SIDEBAR -->
        <div id="sidebar">
            <?php View::Block ('ad300.tpl'); ?>
            <?php View::OutputSidebarBlocks(); ?>
        </div>
        <!-- END SIDEBAR -->

        <br clear="all" />

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