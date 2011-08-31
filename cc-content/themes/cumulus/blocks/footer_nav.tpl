
<!-- BEGIN FOOTER -->
<div id="footer">
    <div id="footer-sub">

        <div id="footer-left">
            <form action="<?=HOST?>/search/" method="post">
                <input class="defaultText" title="<?=Language::GetText('search_text')?>" type="text" name="keyword" value="<?=Language::GetText('search_text')?>" />
                <input type="hidden" name="submitted_search" value="TRUE" />
            </form>
            <ul class="link-col wider">
                <li><a href="" title="<?=Language::GetCustomText('most_discussed_videos')?>"><?=Language::GetCustomText('most_discussed_videos')?></a></li>
                <li><a href="" title="<?=Language::GetCustomText('highest_rated_videos')?>"><?=Language::GetCustomText('highest_rated_videos')?></a></li>
            </ul>
            <ul class="link-col">
                <li><a href="" title="<?=Language::GetCustomText('faq')?>"><?=Language::GetCustomText('faq')?></a></li>
                <li><a href="" title="<?=Language::GetCustomText('need_help')?>"><?=Language::GetCustomText('need_help')?></a></li>
            </ul>
        </div>

        <div id="footer-right">
            <div id="quick-links"><span>Quick Links</span></div>
            <ul class="link-col">
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('about')?>"><?=Language::GetCustomText('about')?></a></li>
                <li><a href="<?=HOST?>/contact/" title="<?=Language::GetCustomText('contact')?>"><?=Language::GetCustomText('contact')?></a></li>
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('careers')?>"><?=Language::GetCustomText('careers')?></a></li>
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('sitemap')?>"><?=Language::GetCustomText('sitemap')?></a></li>
            </ul>
            <ul class="link-col">
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('terms')?>"><?=Language::GetCustomText('terms')?></a></li>
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('privacy')?>"><?=Language::GetCustomText('privacy')?></a></li>
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('copyright')?>"><?=Language::GetCustomText('copyright')?></a></li>
                <li><a href="<?=HOST?>/" title="<?=Language::GetCustomText('advertising')?>"><?=Language::GetCustomText('advertising')?></a></li>
            </ul>
        </div>

    </div>
</div>
<!-- END FOOTER -->
