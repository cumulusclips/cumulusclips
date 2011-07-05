
<!-- BEGIN HEADER -->
<div id="header">
    <div id="header-sub">
        <a id="logo" href="<?=HOST?>/" title="Cumulus - Free Video Sharing CMS, Video Sharing Script, YouTube Clone Script"><img src="<?=THEME?>/images/logo.png" /></a>
        <form action="<?=HOST?>/search/" method="post">
            <input class="defaultText" title="<?=Language::GetText('search_text')?>" type="text" name="keyword" value="<?=Language::GetText('search_text')?>" />
            <input type="hidden" name="submitted_search" value="TRUE" />
        </form>
        <div id="welcome">

            <?php if ($logged_in): ?>
                <a href="<?=HOST?>/logout/" title="<?=Language::GetText('logout')?>"><?=Language::GetText('logout')?></a>
                <a href="<?=HOST?>/myaccount/" title="<?=Language::GetText('myaccount')?>"><?=Language::GetText('myaccount')?></a>
            <?php else: ?>
                <a href="<?=HOST?>/login/" title="<?=Language::GetText('login')?>"><?=Language::GetText('login')?></a>
                <a href="<?=HOST?>/register/" title="<?=Language::GetText('register')?>"><?=Language::GetText('register')?></a>
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
