
<p class="large"><?=Language::GetText('recent_posts_header')?></p>
<div class="block">
    
    <?php while ($row = $db->FetchObj ($result_posts)): ?>

        <?php $post = new Post ($row->post_id); ?>
        <p class="post">
            <?=$post->post?><br />
            <strong><?=Functions::TimeSince (strtotime ($post->date_created))?></strong>
        </p>
    
    <?php endwhile; ?>

</div>
