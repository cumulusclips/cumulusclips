<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="baseURL" content="<?=HOST?>" />
<?php Functions::AdminOutputMeta(); ?>
<title><?=$page_title?></title>
<link rel="shortcut icon" type="image/x-icon" href="<?=HOST?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/tipsy/stylesheets/tipsy.css" />
<?php Functions::AdminOutputCss(); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/css/admin.css" />
</head>
<body>
<div id="wrapper">

    <div id="header">
        <a id="logo" href="<?=ADMIN?>/" title="CumulusClips Admin Panel">CumulusClips Admin Panel</a>
        <div id="header-menu">
            <a href="<?=HOST?>/" title="View Site" target="_ccsite">View Site</a> &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="<?=HOST?>/logout/" title="Logout">Logout</a>
        </div>
    </div>

    <div id="sidebar" class="block">

        <div class="menu">
            <?php $dashboardMenuOpen = Functions::isPanelOpen('dashboard'); ?>
            <a href="#menu-dashboard" data-toggle="collapse" class="menu-toggle <?=($dashboardMenuOpen) ? 'open' : ''?>">Dashboard</a>
            <div id="menu-dashboard" class="collapse <?=($dashboardMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/">Dashboard</a></p>
                <p><a href="<?=ADMIN?>/logs.php">System Logs</a></p>
                <p><a href="<?=ADMIN?>/updates.php">Updates</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $videoMenuOpen = Functions::isPanelOpen('videos'); ?>
            <a href="#menu-videos" data-toggle="collapse" class="menu-toggle <?=($videoMenuOpen) ? 'open' : ''?>">Videos</a>
            <div id="menu-videos" class="collapse <?=($videoMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/videos.php">Browse Videos</a></p>
                <p><a href="<?=ADMIN?>/videos_add.php">Add New Video</a></p>
                <p><a href="<?=ADMIN?>/videos_categories.php">Video Categories</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $membersMenuOpen = Functions::isPanelOpen('members'); ?>
            <a href="#menu-members" data-toggle="collapse" class="menu-toggle <?=($membersMenuOpen) ? 'open' : ''?>">Members</a>
            <div id="menu-members" class="collapse <?=($membersMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/members.php?status=active">Browse Members</a></p>
                <p><a href="<?=ADMIN?>/members.php?status=pending">Pending Members</a></p>
                <p><a href="<?=ADMIN?>/members_add.php">Add New Member</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $commentsMenuOpen = Functions::isPanelOpen('comments'); ?>
            <a href="#menu-comments" data-toggle="collapse" class="menu-toggle <?=($commentsMenuOpen) ? 'open' : ''?>">Comments</a>
            <div id="menu-comments" class="collapse <?=($commentsMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/comments.php?status=approved">Approved Comments</a></p>
                <p><a href="<?=ADMIN?>/comments.php?status=pending">Pending Comments</a></p>
                <p><a href="<?=ADMIN?>/comments.php?status=banned">Banned Comments</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $flagsMenuOpen = Functions::isPanelOpen('flags'); ?>
            <a href="#menu-flags" data-toggle="collapse" class="menu-toggle <?=($flagsMenuOpen) ? 'open' : ''?>">Flags</a>
            <div id="menu-flags" class="collapse <?=($flagsMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/flags.php?status=video">Flagged Videos</a></p>
                <p><a href="<?=ADMIN?>/flags.php?status=user">Flagged Members</a></p>
                <p><a href="<?=ADMIN?>/flags.php?status=comment">Flagged Comments</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $pagesMenuOpen = Functions::isPanelOpen('pages'); ?>
            <a href="#menu-pages" data-toggle="collapse" class="menu-toggle <?=($pagesMenuOpen) ? 'open' : ''?>">Pages</a>
            <div id="menu-pages" class="collapse <?=($pagesMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/pages.php">Browse Pages</a></p>
                <p><a href="<?=ADMIN?>/pages_add.php">Add New Page</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $appearanceMenuOpen = Functions::isPanelOpen('appearance'); ?>
            <a href="#menu-appearance" data-toggle="collapse" class="menu-toggle <?=($appearanceMenuOpen) ? 'open' : ''?>">Appearance</a>
            <div id="menu-appearance" class="collapse <?=($appearanceMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/themes.php">Themes</a></p>
                <p><a href="<?=ADMIN?>/themes_add.php">Add New Theme</a></p>
                <p><a href="<?=ADMIN?>/languages.php">Languages</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $pluginsMenuOpen = Functions::isPanelOpen('plugins'); ?>
            <a href="#menu-plugins" data-toggle="collapse" class="menu-toggle <?=($pluginsMenuOpen) ? 'open' : ''?>">Plugins</a>
            <div id="menu-plugins" class="collapse <?=($pluginsMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/plugins.php">Plugins</a></p>
                <p><a href="<?=ADMIN?>/plugins_add.php">Add New Plugin</a></p>
            </div>
        </div>

        <div class="menu">
            <?php $settingsMenuOpen = Functions::isPanelOpen('settings'); ?>
            <a href="#menu-settings" data-toggle="collapse" class="menu-toggle <?=($settingsMenuOpen) ? 'open' : ''?>">Settings</a>
            <div id="menu-settings" class="collapse <?=($settingsMenuOpen) ? 'in' : ''?>">
                <p><a href="<?=ADMIN?>/settings.php">General</a></p>
                <p><a href="<?=ADMIN?>/settings_video.php">Video</a></p>
                <p><a href="<?=ADMIN?>/settings_email.php">Email</a></p>
            </div>
        </div>

    </div>

    <div id="container">
        <div id="main">

            <?php if (!empty ($_SESSION['updates_available']) && !isset ($dont_show_update_prompt)): ?>

                <?php $updates_available = unserialize($_SESSION['updates_available']); ?>
                <div id="updates-available" class="message notice">
                    An updated version of CumulusClips (version <?=$updates_available->version?>)
                    is available! Please <a href="<?=ADMIN?>/updates.php">update now</a>.
                </div>

            <?php endif; ?>