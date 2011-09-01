
<!-- BEGIN HEADER -->
<div id="header">
    <div id="header-sub">
        <a id="logo" href="<?=HOST?>/" title="Cumulus - Free Video Sharing CMS, Video Sharing Script, YouTube Clone Script"><span>Cumulus - Free Video Sharing CMS, Video Sharing Script, YouTube Clone Script</span></a>
        <form action="<?=HOST?>/search/" method="post">
            <input class="defaultText" title="<?=Language::GetText('search_text')?>" type="text" name="keyword" value="<?=Language::GetText('search_text')?>" />
            <input type="hidden" name="submitted_search" value="TRUE" />
        </form>
        <div id="welcome">

            <?php if ($logged_in): ?>
                <a href="<?=HOST?>/logout/" title="<?=Language::GetText('logout')?>"><?=Language::GetText('logout')?></a>
                <a href="<?=HOST?>/myaccount/" title="<?=Language::GetText('myaccount')?>"><?=Language::GetText('myaccount')?></a>

                <?php if (User::CheckPermissions ('admin_panel', $user)): ?>
                    <a href="<?=HOST?>/cc-admin/" title="<?=Language::GetText('admin_panel')?>"><?=Language::GetText('admin_panel')?></a>
                <?php endif ?>

            <?php else: ?>
                <a href="<?=HOST?>/login/" title="<?=Language::GetText('login')?>"><?=Language::GetText('login')?></a>
                <a href="<?=HOST?>/register/" title="<?=Language::GetText('register')?>"><?=Language::GetText('register')?></a>
            <?php endif; ?>

            <?php $active_languages = Language::GetActiveLanguages(); ?>
            <?php if (count ($active_languages) > 1): ?>

                <span class="languages">
                    <a class="active-language" href=""><?=Language::GetLanguage (true)?></a>
                    <span class="arrow"></span>
                    <ul>
                        <?php foreach ($active_languages as $key => $lang): ?>
                            <li><a href="<?=HOST?>/language/set/<?=$key?>/"><?=$lang['native_name']?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </span>

            <?php endif; ?>

        </div>
        <div id="nav">
            <a href="<?=HOST?>/videos/" title="<?=Language::GetText('videos')?>"><?=Language::GetText('videos')?></a>
            <a href="<?=HOST?>/members/" title="<?=Language::GetText('members')?>"><?=Language::GetText('members')?></a>
            <a href="<?=HOST?>/myaccount/upload/" title="<?=Language::GetText('upload')?>"><?=Language::GetText('upload')?></a>
        </div>
    </div>
</div>
<!-- END HEADER -->
