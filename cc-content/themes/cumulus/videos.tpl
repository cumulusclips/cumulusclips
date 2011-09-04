<?php View::Header(); ?>

<h1><?=Language::GetText('videos_header')?></h1>
<p class="large"><?=Language::GetText('viewing')?>: <?=$sub_header?></p>


<div class="block">
    <div id="sort-options">
        <p class="big"><?=Language::GetText('grouping')?></p>
        <p><a href="<?=HOST?>/videos/most-recent/" title="<?=Language::GetText('most_recent')?>"><?=Language::GetText('most_recent')?></a><br />
        <a href="<?=HOST?>/videos/most-viewed/" title="<?=Language::GetText('most_viewed')?>"><?=Language::GetText('most_viewed')?></a><br />
        <a href="<?=HOST?>/videos/most-discussed/" title="<?=Language::GetText('most_discussed')?>"><?=Language::GetText('most_discussed')?></a><br />
        <a href="<?=HOST?>/videos/most-rated/" title="<?=Language::GetText('most_rated')?>"><?=Language::GetText('most_rated')?></a></p>
    </div>

    <div id="category-options">
        <p class="big"><?=Language::GetText('category')?></p>
        <ul>
        <?php foreach ($category_list as $cat_id): ?>
            <?php $category = new Category ($cat_id); ?>
            <li><a href="<?=HOST?>/videos/<?=$category->slug?>/" title="<?=$category->cat_name?>"><?=$category->cat_name?></a></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>


<?php if (!empty ($browse_videos)): ?>
    <?php View::RepeatingBlock('video.tpl', $browse_videos); ?>
    <?=$pagination->Paginate()?>
<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_videos')?></strong></div>
<?php endif; ?>

<?php View::Footer(); ?>