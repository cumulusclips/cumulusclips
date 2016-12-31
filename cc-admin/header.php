<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="baseURL" content="<?=HOST?>" />
<?php Functions::AdminOutputMeta(); ?>
<title><?=$page_title?></title>
<link rel="shortcut icon" type="image/x-icon" href="<?=HOST?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/tipsy/stylesheets/tipsy.css" />
<?php Functions::adminOutputCss(); ?>
<link rel="stylesheet" href="<?=ADMIN?>/extras/bootstrap-3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/font-awesome-4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/css/admin.css?v<?=CURRENT_VERSION?>" />
</head>
<body>




<header>
    <div id="header">
        <a id="logo" href="<?=ADMIN?>/" title="CumulusClips Admin Panel">
            <img src="<?=ADMIN?>/images/admin-logo.png" alt="CumulusClips" />
        </a>
        <div class="menu">
            <a href="<?=HOST?>/" title="View Site" target="_ccsite">View Site</a> &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="<?=HOST?>/logout/" title="Logout">Logout</a>
        </div>
    </div>
</header>

<div class="container">

    <!-- Begin Sidebar -->
    <div id="sidebar">

        <div class="menu">
            <?php $dashboardMenuOpen = Functions::isPanelOpen('dashboard'); ?>
            <a href="#menu-dashboard" data-toggle="collapse" class="icon-dashboard <?=($dashboardMenuOpen) ? '' : 'collapsed'?>"><span>Dashboard</span></a>
            <ul id="menu-dashboard" class="collapse <?=($dashboardMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'dashboard') ? 'active' : ''?>"><a href="<?=ADMIN?>/">Dashboard</a></li>
                <?php if ($userService->checkPermissions('manage_settings')): ?>
                    <li class="<?=($pageName == 'logs') ? 'active' : ''?>"><a href="<?=ADMIN?>/logs.php">System Logs</a></li>
                    <li class="<?=($pageName == 'updates') ? 'active' : ''?>"><a href="<?=ADMIN?>/updates.php">Updates</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="menu">
            <?php $videoMenuOpen = Functions::isPanelOpen('videos'); ?>
            <a href="#menu-videos" data-toggle="collapse" class="icon-video <?=($videoMenuOpen) ? '' : 'collapsed'?>"><span>Videos</span></a>
            <ul id="menu-videos" class="collapse <?=($videoMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'videos') ? 'active' : ''?>"><a href="<?=ADMIN?>/videos.php">Browse Videos</a></li>
                <li class="<?=($pageName == 'videos-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/videos_add.php">Add New Video</a></li>
                <li class="<?=($pageName == 'videos-imports') ? 'active' : ''?>"><a href="<?=ADMIN?>/videos_imports.php">Video Imports</a></li>
                <li class="<?=($pageName == 'videos-categories') ? 'active' : ''?>"><a href="<?=ADMIN?>/videos_categories.php">Video Categories</a></li>
            </ul>
        </div>

        <div class="menu">
            <?php $membersMenuOpen = Functions::isPanelOpen('members'); ?>
            <a href="#menu-members" data-toggle="collapse" class="icon-members <?=($membersMenuOpen) ? '' : 'collapsed'?>"><span>Members</span></a>
            <ul id="menu-members" class="collapse <?=($membersMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'members') ? 'active' : ''?>"><a href="<?=ADMIN?>/members.php?status=active">Browse Members</a></li>
                <li class="<?=($pageName == 'members-pending') ? 'active' : ''?>"><a href="<?=ADMIN?>/members.php?status=pending">Pending Members</a></li>
                <li class="<?=($pageName == 'members-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/members_add.php">Add New Member</a></li>
            </ul>
        </div>

        <div class="menu">
            <?php $commentsMenuOpen = Functions::isPanelOpen('comments'); ?>
            <a href="#menu-comments" data-toggle="collapse" class="icon-comment <?=($commentsMenuOpen) ? '' : 'collapsed'?>"><span>Comments</span></a>
            <ul id="menu-comments" class="collapse <?=($commentsMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'comments-approved') ? 'active' : ''?>"><a href="<?=ADMIN?>/comments.php?status=approved">Approved Comments</a></li>
                <li class="<?=($pageName == 'comments-pending') ? 'active' : ''?>"><a href="<?=ADMIN?>/comments.php?status=pending">Pending Comments</a></li>
                <li class="<?=($pageName == 'comments-banned') ? 'active' : ''?>"><a href="<?=ADMIN?>/comments.php?status=banned">Banned Comments</a></li>
            </ul>
        </div>

        <div class="menu">
            <?php $flagsMenuOpen = Functions::isPanelOpen('flags'); ?>
            <a href="#menu-flags" data-toggle="collapse" class="icon-flag <?=($flagsMenuOpen) ? '' : 'collapsed'?>"><span>Flags</span></a>
            <ul id="menu-flags" class="collapse <?=($flagsMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'flags-videos') ? 'active' : ''?>"><a href="<?=ADMIN?>/flags.php?status=video">Flagged Videos</a></li>
                <li class="<?=($pageName == 'flags-members') ? 'active' : ''?>"><a href="<?=ADMIN?>/flags.php?status=user">Flagged Members</a></li>
                <li class="<?=($pageName == 'flags-comments') ? 'active' : ''?>"><a href="<?=ADMIN?>/flags.php?status=comment">Flagged Comments</a></li>
            </ul>
        </div>

        <div class="menu">
            <?php $pagesMenuOpen = Functions::isPanelOpen('pages'); ?>
            <a href="#menu-pages" data-toggle="collapse" class="icon-pages <?=($pagesMenuOpen) ? '' : 'collapsed'?>"><span>Pages</span></a>
            <ul id="menu-pages" class="collapse <?=($pagesMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'pages') ? 'active' : ''?>"><a href="<?=ADMIN?>/pages.php">Browse Pages</a></li>
                <li class="<?=($pageName == 'pages-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/pages_add.php">Add New Page</a></li>
            </ul>
        </div>

        <div class="menu">
            <?php $libraryMenuOpen = Functions::isPanelOpen('library'); ?>
            <a href="#menu-library" data-toggle="collapse" class="icon-library <?=($libraryMenuOpen) ? '' : 'collapsed'?>"><span>File Library</span></a>
            <ul id="menu-library" class="collapse <?=($libraryMenuOpen) ? 'in' : ''?>">
                <li class="<?=($pageName == 'library') ? 'active' : ''?>"><a href="<?=ADMIN?>/library.php">Browse File Library</a></li>
                <li class="<?=($pageName == 'library-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/library_add.php">Add New File</a></li>
            </ul>
        </div>

        <?php if ($userService->checkPermissions('manage_settings')): ?>
            <div class="menu">
                <?php $appearanceMenuOpen = Functions::isPanelOpen('appearance'); ?>
                <a href="#menu-appearance" data-toggle="collapse" class="icon-appearance <?=($appearanceMenuOpen) ? '' : 'collapsed'?>"><span>Appearance</span></a>
                <ul id="menu-appearance" class="collapse <?=($appearanceMenuOpen) ? 'in' : ''?>">
                    <li class="<?=($pageName == 'customizations') ? 'active' : ''?>"><a href="<?=ADMIN?>/customizations.php">Customizations</a></li>
                    <li class="<?=($pageName == 'themes') ? 'active' : ''?>"><a href="<?=ADMIN?>/themes.php">Themes</a></li>
                    <li class="<?=($pageName == 'themes-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/themes_add.php">Add New Theme</a></li>
                    <li class="<?=($pageName == 'languages') ? 'active' : ''?>"><a href="<?=ADMIN?>/languages.php">Languages</a></li>
                </ul>
            </div>

            <div class="menu">
                <?php $pluginsMenuOpen = Functions::isPanelOpen('plugins'); ?>
                <a href="#menu-plugins" data-toggle="collapse" class="icon-plugin <?=($pluginsMenuOpen) ? '' : 'collapsed'?>"><span>Plugins</span></a>
                <ul id="menu-plugins" class="collapse <?=($pluginsMenuOpen) ? 'in' : ''?>">
                    <li class="<?=($pageName == 'plugins') ? 'active' : ''?>"><a href="<?=ADMIN?>/plugins.php">Plugins</a></li>
                    <li class="<?=($pageName == 'plugins-add') ? 'active' : ''?>"><a href="<?=ADMIN?>/plugins_add.php">Add New Plugin</a></li>
                </ul>
            </div>

            <div class="menu">
                <?php $settingsMenuOpen = Functions::isPanelOpen('settings'); ?>
                <a href="#menu-settings" data-toggle="collapse" class="icon-settings <?=($settingsMenuOpen) ? '' : 'collapsed'?>"><span>Settings</span></a>
                <ul id="menu-settings" class="collapse <?=($settingsMenuOpen) ? 'in' : ''?>">
                    <li class="<?=($pageName == 'settings') ? 'active' : ''?>"><a href="<?=ADMIN?>/settings.php">General</a></li>
                    <li class="<?=($pageName == 'settings-videos') ? 'active' : ''?>"><a href="<?=ADMIN?>/settings_video.php">Video</a></li>
                    <li class="<?=($pageName == 'settings-email') ? 'active' : ''?>"><a href="<?=ADMIN?>/settings_email.php">Email</a></li>
                </ul>
            </div>
        <?php endif; ?>

    </div>
    <!-- End Sidebar -->

    <!-- Begin Main Content -->
    <main id="<?=$pageName?>">

    <?php if (!empty ($_SESSION['updates_available']) && !isset ($dont_show_update_prompt)): ?>
        <?php $updates_available = unserialize($_SESSION['updates_available']); ?>
        <div id="updates-available" class="alert alert-warning">
            An updated version of CumulusClips (version <?=$updates_available->version?>)
            is available! Please <a href="<?=ADMIN?>/updates.php">update now</a>.
        </div>
    <?php endif; ?>
