<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Techie, Videos, Techie Videos, Tech Videos, How-to videos, Screencasts, Tutorials, Do-it-yourself videos" />
<meta name="description" content="How-to and instructional videos on tech related tasks as well as screencasts, tutorials, and news for techies and tech enthusiasts" />
<title><?php echo $page_title; ?></title>
<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
<link href="/css/main.css" rel="stylesheet" type="text/css" />
<link href="/css/portal.css" rel="stylesheet" type="text/css" />
<link href="/css/admin.css" rel="stylesheet" type="text/css" />
<link rel="alternate" type="application/rss+xml" title="Recently Added Videos" href="<?php echo HOST; ?>/feed/" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/functions.js"></script>
</head>
<body>
<?php include (THEMES . '/blocks/header.tpl'); ?>


    <div id="left">

        <div class="short-block-header" id="account-menu-header"><h1>Account Menu</h1></div>
        <div class="short-block">

            <h3>User Management</h3>
            <ul>
                <li><a href="<?php echo HOST; ?>/admin/users/" title="Browse Users">Browse Users</a></li>
            </ul>

            <h3>Services</h3>
            <ul>
                <li><a href="<?php echo HOST; ?>/admin/start-encoding/" title="Start Encoding">Start Encoding</a></li>
            </ul>

        </div>

        <p id="ad-title">Advertisement</p>
        <div id="side-ad">
            <?php include ($sidebar_ad_250); ?>
        </div>

    </div>


    <div id="right">
        <?php include (THEMES . '/' . $content_file); ?>
    </div>
    <br clear="all" />

<?php include (THEMES . '/blocks/footer.tpl'); ?>