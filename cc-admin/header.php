<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="baseURL" content="<?=HOST?>" />
<?php Functions::AdminOutputMeta(); ?>
<title><?=$page_title?></title>
<link rel="shortcut icon" type="image/x-icon" href="<?=HOST?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/css/reset.css" />
<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/tipsy/stylesheets/tipsy.css" />
<?php Functions::AdminOutputCss(); ?>
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

        <div class="panel<?=(Functions::IsPanelOpen('dashboard'))?' open-panel down-icon':''?>">
            <h3 class="dashboard"><span>Dashboard</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/">Dashboard</a></p>
                <p><a href="<?=ADMIN?>/logs.php">System Logs</a></p>
                <p><a href="<?=ADMIN?>/updates.php">Updates</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('videos'))?' open-panel down-icon':''?>">
            <h3 class="videos"><span>Videos</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/videos.php">Browse Videos</a></p>
                <p><a href="<?=ADMIN?>/videos_add.php">Add New Video</a></p>
                <p><a href="<?=ADMIN?>/videos_categories.php">Video Categories</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('members'))?' open-panel down-icon':''?>">
            <h3 class="members"><span>Members</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/members.php?status=active">Browse Members</a></p>
                <p><a href="<?=ADMIN?>/members.php?status=pending">Pending Members</a></p>
                <p><a href="<?=ADMIN?>/members_add.php">Add New Member</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('comments'))?' open-panel down-icon':''?>">
            <h3 class="comments"><span>Comments</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/comments.php?status=approved">Approved Comments</a></p>
                <p><a href="<?=ADMIN?>/comments.php?status=pending">Pending Comments</a></p>
                <p><a href="<?=ADMIN?>/comments.php?status=banned">Banned Comments</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('flags'))?' open-panel down-icon':''?>">
            <h3 class="flags"><span>Flags</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/flags.php?status=video">Flagged Videos</a></p>
                <p><a href="<?=ADMIN?>/flags.php?status=member">Flagged Members</a></p>
                <p><a href="<?=ADMIN?>/flags.php?status=comment">Flagged Comments</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('pages'))?' open-panel down-icon':''?>">
            <h3 class="pages"><span>Pages</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/pages.php">Browse Pages</a></p>
                <p><a href="<?=ADMIN?>/pages_add.php">Add New Page</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('appearance'))?' open-panel down-icon':''?>">
            <h3 class="appearance"><span>Appearance</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/themes.php">Themes</a></p>
                <p><a href="<?=ADMIN?>/themes_add.php">Add New Theme</a></p>
                <p><a href="<?=ADMIN?>/languages.php">Languages</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('plugins'))?' open-panel down-icon':''?>">
            <h3 class="plugins"><span>Plugins</span></h3>
            <div>
                <p><a href="<?=ADMIN?>/plugins.php">Plugins</a></p>
                <p><a href="<?=ADMIN?>/plugins_add.php">Add New Plugin</a></p>
            </div>
        </div>

        <div class="panel<?=(Functions::IsPanelOpen('settings'))?' open-panel down-icon':''?>">
            <h3 class="settings"><span>Settings</span></h3>
            <div>
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
                <div id="updates-available" class="notice">
                    An updated version of CumulusClips (version <?=$updates_available->version?>)
                    is available! Please <a href="<?=ADMIN?>/updates.php">update now</a>.
                </div>

            <?php endif; ?>