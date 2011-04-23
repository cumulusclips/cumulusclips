<!DOCTYPE html>
<html>
<head>
<title><?=View::$vars->meta->title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="CumulusClips" />
<meta name="keywords" content="<?=View::$vars->meta->keywords?>" />
<meta name="description" content="<?=View::$vars->meta->description?>" />
<?php View::WriteMeta(); ?>
<link rel="stylesheet" type="text/css" href="<?=THEME?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?=THEME?>/css/portal.css" />
<?php View::WriteCSS(); ?>

</head>
<body class="<?=Language::GetCSSName()?>">

<!-- BEGIN WRAPPER -->
<div id="wrapper">

    <?php View::Header(); ?>

    <!-- BEGIN MAIN -->
    <div id="main">

        <!-- BEGIN SIDEBAR -->
        <div id="portal-sidebar">

            <p class="large"><?=Language::GetText('account_menu_header')?></p>
            <div class="block" id="menu">

                <p class="big"><?=Language::GetText('manage_media')?></p>
                <ul>
                    <li><a href="<?php echo HOST; ?>/myaccount/upload/" title="<?=Language::GetText('upload_video')?>"><?=Language::GetText('upload_video')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/myvideos/" title="<?=Language::GetText('my_videos')?>"><?=Language::GetText('my_videos')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/myfavorites/" title="<?=Language::GetText('my_favorite_videos')?>"><?=Language::GetText('my_favorite_videos')?></a></li>
                </ul>

                <p class="big"><?=Language::GetText('account_settings')?></p>
                <ul>
                    <li><a href="<?php echo HOST; ?>/myaccount/profile/" title="<?=Language::GetText('update_profile')?>"><?=Language::GetText('update_profile')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/privacy-settings/" title="<?=Language::GetText('privacy_settings')?>"><?=Language::GetText('privacy_settings')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/change-password/" title="<?=Language::GetText('change_password')?>"><?=Language::GetText('change_password')?></a></li>
                </ul>

                <p class="big"><?=Language::GetText('community')?></p>
                <ul>
                    <li><a href="<?php echo HOST; ?>/myaccount/subscriptions/" title="<?=Language::GetText('my_subscriptions')?>"><?=Language::GetText('my_subscriptions')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/subscribers/" title="<?=Language::GetText('my_subscribers')?>"><?=Language::GetText('my_subscribers')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/inbox/" title="<?=Language::GetText('inbox')?>"><?=Language::GetText('inbox')?></a></li>
                    <li><a href="<?php echo HOST; ?>/myaccount/message/" title="<?=Language::GetText('send_message')?>"><?=Language::GetText('send_message')?></a></li>
                </ul>

            </div>

            <?php View::OutputSidebarBlocks(); ?>

        </div>
        <!-- END SIDEBAR -->


        <!-- BEGIN CONTENT -->
        <div id="portal-content">
            <?php View::Body(); ?>
        </div>
        <!-- END CONTENT -->


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