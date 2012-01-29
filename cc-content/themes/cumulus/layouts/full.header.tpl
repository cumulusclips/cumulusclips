<!DOCTYPE html>
<html>
<head>
<title><?=$meta->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="baseURL" content="<?=HOST?>" />
<?php View::WriteMeta(); ?>
<?php Plugin::Trigger ('theme.head'); ?>
<link rel="shortcut icon" type="image/x-icon" href="<?=HOST?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=$config->theme_url?>/css/reset.css" />
<link rel="stylesheet" type="text/css" href="<?=$config->theme_url?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?=$config->theme_url?>/css/pages.css" />
<?php View::WriteCSS(); ?>

</head>
<body class="<?=View::CssHooks()?>">
<?php Plugin::Trigger ('theme.body'); ?>

<!-- BEGIN WRAPPER -->
<div id="wrapper">

    <?php View::Block ('header_nav.tpl'); ?>

    <!-- BEGIN MAIN -->
    <div id="main">
