<?php View::Header(); ?>

<h1><?=Language::GetText('search_header')?></h1>

<p class="post-header"><strong><?=Language::GetText('results_for')?>: '<?php echo $cleaned; ?>'</strong></p>

<?php if (!empty ($search_videos)): ?>
    <?php View::RepeatingBlock('video.tpl', $search_videos); ?>
    <?=$pagination->Paginate()?>
<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_results')?></strong></div>
<?php endif; ?>

<?php View::Footer(); ?>