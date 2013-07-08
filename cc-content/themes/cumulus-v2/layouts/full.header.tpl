<!DOCTYPE html>
<html>
<head>
<title><?=$meta->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="baseUrl" content="<?=HOST?>" />
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
    
    <!-- BEGIN HEADER -->
    <div id="header">
        <div id="sub_header">
            <a id="logo" href="<?=HOST?>/" title="CumulusClips - Free Video Sharing CMS, Video Sharing Script, YouTube Clone Script">CumulusClips - Free Video Sharing CMS, Video Sharing Script, YouTube Clone Script</a>
            <div id="header_right">
                
            <?php if ($logged_in): ?>
            
                <a href="<?=HOST?>/logout/" title="<?=Language::GetText('logout')?>"><?=Language::GetText('logout')?></a>
                <a href="<?=HOST?>/myaccount/" title="<?=Language::GetText('myaccount')?>"><?=Language::GetText('myaccount')?></a>
                <a title="<?=Language::GetText('view_my_profile')?>" href="<?=HOST?>/members/<?=$user->username?>/"><?=Language::GetText('view_my_profile')?></a>

                <?php if (User::CheckPermissions ('admin_panel', $user)): ?>
                    <a href="<?=HOST?>/cc-admin/" title="<?=Language::GetText('admin_panel')?>"><?=Language::GetText('admin_panel')?></a>
                <?php endif ?>

            <?php else: ?>
                <a href="<?=HOST?>/login/" title="<?=Language::GetText('login')?>"><?=Language::GetText('login')?></a>
                <a href="<?=HOST?>/register/" title="<?=Language::GetText('register')?>"><?=Language::GetText('register')?></a>
            <?php endif; ?>       
                
                <form action="<?=HOST?>/search/" method="post">
                    <input class="defaultText" title="<?=Language::GetText('search_text')?>" type="text" name="keyword" value="<?=Language::GetText('search_text')?>" />
                    <input type="hidden" name="submitted_search" value="TRUE" />
                </form>
            </div>
        </div>
    </div>
    <!-- END HEADER -->
    
    <?php View::Block ('header_nav.tpl'); ?>
    
    <!-- BEGIN MAIN CONTAINER -->
    <div id="retainer">
        <div id="main">
                
