
<!-- BEGIN FOOTER -->
<div id="footer">
    <div id="footer-sub">

        <div id="footer-left">
            <form action="<?=HOST?>/search/" method="post">
                <input class="defaultText" title="<?=Language::GetText('search_text')?>" type="text" name="keyword" value="<?=Language::GetText('search_text')?>" />
                <input type="hidden" name="submitted_search" value="TRUE" />
            </form>
            <ul class="link-col wider">
                <li><a href="<?=HOST?>/videos/most-discussed/" title="<?=Language::GetCustomText('most_discussed_videos')?>"><?=Language::GetCustomText('most_discussed_videos')?></a></li>
                <li><a href="<?=HOST?>/videos/most-rated/" title="<?=Language::GetCustomText('highest_rated_videos')?>"><?=Language::GetCustomText('highest_rated_videos')?></a></li>
            </ul>
            <ul class="link-col">
                <li><a href="http://cumulusclips.org/" title="CumulusClips - Free Video Sharing CMS, Free Video Sharing Script, Free Video Sharing Software, YouTube Clone Script">CumulusClips</a></li>
                <li><a href="http://cumulusclips.org/docs/" title="<?=Language::GetCustomText('documentation')?>"><?=Language::GetCustomText('documentation')?></a></li>
            </ul>
        </div>

        <div id="footer-right">
            <div id="quick-links"><span>Quick Links</span></div>
            <ul class="link-col">
                <li><a href="<?=HOST?>/" title="<?=Language::GetText('home')?>"><?=Language::GetText('home')?></a></li>
                <li><a href="<?=HOST?>/videos/" title="<?=Language::GetText('videos')?>"><?=Language::GetText('videos')?></a></li>
                <li><a href="<?=HOST?>/members/" title="<?=Language::GetText('members')?>"><?=Language::GetText('members')?></a></li>
                <li><a href="<?=HOST?>/myaccount/upload/" title="<?=Language::GetText('upload')?>"><?=Language::GetText('upload')?></a></li>
            </ul>
            <ul class="link-col">
                <li><a href="<?=HOST?>/contact/" title="<?=Language::GetCustomText('contact')?>"><?=Language::GetCustomText('contact')?></a></li>
                <li><a href="<?=HOST?>/sample-page/" title="<?=Language::GetCustomText('sample')?>"><?=Language::GetCustomText('sample')?></a></li>
                <li><a href="<?=HOST?>/feed/" title="<?=Language::GetCustomText('rss')?>"><?=Language::GetCustomText('rss')?></a></li>
                <li><a href="<?=HOST?>/video-sitemap.xml" title="<?=Language::GetCustomText('sitemap')?>"><?=Language::GetCustomText('sitemap')?></a></li>
            </ul>
        </div>

    </div>
</div>
<!-- END FOOTER -->
