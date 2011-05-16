<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$page_title?></title>
<link rel="shortcut icon" href="<?=HOST?>/favicon.ico" type="image/x-icon" />
<link href="<?=THEME?>/css/admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?=THEME?>/js/jquery.js"></script>
<script type="text/javascript" src="<?=THEME?>/js/functions.js"></script>
</head>
<body>
<?php include (THEME_PATH . '/header.tpl'); ?>


    <div id="sidebar">

        <h3>Users</h3>
        <ul>
            <li><a href="<?php echo HOST; ?>/admin/users/" title="Browse Users">Browse Users</a></li>
        </ul>

    </div>


    <div id="main">
        <?php include (THEME_PATH . "/$content"); ?>
    </div>

<?php include (THEME_PATH . '/footer.tpl'); ?>