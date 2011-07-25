<!DOCTYPE html>
<html>
<head>
<title><?=$meta->title?></title>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
<meta name="baseURL" content="<?=HOST?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php View::WriteMeta(); ?>
<link rel="apple-touch-icon" href="<?=THEME?>/images/touch.png"/>
<link href="<?=THEME?>/css/reset.css" rel="stylesheet" type="text/css" />
<link href="<?=THEME?>/css/main.css" rel="stylesheet" type="text/css" />
<?php View::WriteCSS(); ?>
</head>
<body class="<?=Language::GetCSSName()?>">
    
<div id="header">
    <a href="<?=MOBILE_HOST?>/"><img src="<?=THEME?>/images/logo.png" alt="<?=Language::GetText('mobile_heading', array ('sitename' => Settings::Get('sitename')))?>" /></a>
</div>

<div id="wrapper">
    
    <div id="nav">
        <?php if (View::$options->page == 'mobile_play'): ?>
            <div><a href="" class="back"><?=Language::GetText('back')?></a></div>
        <?php else: ?>
            <div><a href="<?=MOBILE_HOST?>/"><?=Language::GetText('home')?></a></div>
            <div><a href="<?=MOBILE_HOST?>/v/"><?=Language::GetText('videos')?></a></div>
            <div><a href="<?=MOBILE_HOST?>/s/"><?=Language::GetText('search')?></a></div>
        <?php endif; ?>
    </div>

