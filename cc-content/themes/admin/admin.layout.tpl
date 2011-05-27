<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$page_title?></title>
<link rel="shortcut icon" href="<?=HOST?>/favicon.ico" type="image/x-icon" />
<link href="<?=THEME?>/css/reset.css" rel="stylesheet" type="text/css" />
<link href="<?=THEME?>/css/admin.css" rel="stylesheet" type="text/css" />
<link href="<?=THEME?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?=THEME?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?=THEME?>/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=THEME?>/js/cookie.plugin.js"></script>
<script type="text/javascript">
$('document').ready(function(){
    var settings = $.parseJSON($.cookie('cc_admin_settings'));
    console.log(settings);
    var test = new String ('test');
    console.log(test.serialize());
    $("#sidebar h3").click(function(){
        $(this).next().slideToggle('fast');
        console.log($(this).data('name'));
        console.log(settings['sidebar']);

//        settings.sidebar[$(this).data('name')] = 1;
//        $.cookie('cc_admin_settings', settings);
    });
    $('.list tr').hover(function(){$(this).find('.record-actions').toggleClass('invisible');});
});
</script>
</head>
<body>
<div id="wrapper">

    <?php include (THEME_PATH . '/header.tpl'); ?>

    <div id="sidebar" class="block">

        <div class="panel<?=(Functions::IsPanelOpen('dashboard'))?' open':''?> dashboard">
            <h3 data-name="dashboard">Dashboard</h3>
            <div>
                <p><a href="<?=ADMIN?>/">Dashboard</a></p>
                <p><a href="<?=ADMIN?>">Updates</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('videos'))?' open':''?> videos">
            <h3 data-name="">Videos</h3>
            <div>
                <p><a href="<?=ADMIN?>/videos.php?status=6">Approved Videos</a></p>
                <p><a href="<?=ADMIN?>/videos.php?status=9">Pending Videos</a></p>
                <p><a href="<?=ADMIN?>/videos.php?status=5">Processing Videos</a></p>
                <p><a href="<?=ADMIN?>/videos.php?status=7">Banned Videos</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('members'))?' open':''?> members">
            <h3 data-name="">Members</h3>
            <div>
                <p><a href="<?=ADMIN?>/members.php?status=active">Active Members</a></p>
                <p><a href="<?=ADMIN?>/members.php?status=pending">Pending Members</a></p>
                <p><a href="<?=ADMIN?>/members.php?status=banned">Banned Members</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('comments'))?' open':''?> comments">
            <h3 data-name="">Comments</h3>
            <div>
                <p><a href="<?=ADMIN?>">Approved Comments</a></p>
                <p><a href="<?=ADMIN?>">Pending Comments</a></p>
                <p><a href="<?=ADMIN?>">Banned Comments</a></p>
                <p><a href="<?=ADMIN?>">SPAM Comments</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('flags'))?' open':''?> flags">
            <h3 data-name="">Flags</h3>
            <div>
                <p><a href="<?=ADMIN?>">Flagged Videos</a></p>
                <p><a href="<?=ADMIN?>">Flagged Members</a></p>
                <p><a href="<?=ADMIN?>">Flagged Comments</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('pages'))?' open':''?> pages">
            <h3 data-name="">Pages</h3>
            <div>
                <p><a href="<?=ADMIN?>">Add Page</a></p>
                <p><a href="<?=ADMIN?>">Browse Pages</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('appearance'))?' open':''?> appearance">
            <h3 data-name="">Appearance</h3>
            <div>
                <p><a href="<?=ADMIN?>">Themes</a></p>
                <p><a href="<?=ADMIN?>">Languages</a></p>
                <p><a href="<?=ADMIN?>">Banners</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('plugins'))?' open':''?> plugins">
            <h3 data-name="">Plugins</h3>
            <div>
                <p><a href="<?=ADMIN?>">Installed Plugins</a></p>
                <p><a href="<?=ADMIN?>">Add Plugins</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('settings'))?' open':''?> settings">
            <h3 data-name="">Settings</h3>
            <div>
                <p><a href="<?=ADMIN?>">General</a></p>
                <p><a href="<?=ADMIN?>">Alerts</a></p>
            </div>
        </div>

    </div>

    <div id="container">
        <div id="main">
            <?php include (THEME_PATH . "/$content"); ?>
        </div>
    </div>

    <div id="footer-spacer"></div>

</div>

<?php include (THEME_PATH . '/footer.tpl'); ?>