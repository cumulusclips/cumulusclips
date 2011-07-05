
<?php $post = new Post ($_id); ?>
<p class="post">
    <?=$post->post?><br />
    <strong><?=Functions::TimeSince (strtotime ($post->date_created))?></strong>
</p>
