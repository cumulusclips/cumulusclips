<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('update_video_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="form wide">

    <p><a href="<?=HOST?>/myaccount/myvideos/"><?=Language::GetText('back_to_videos')?></a></p><br />
    <form action="<?=HOST?>/myaccount/editvideo/<?=$video->video_id?>/" method="post">

        <label class="<?=(isset ($errors['title'])) ? 'error' : ''?>"><?=Language::GetText('title')?>:</label>
        <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />

        <label class="<?=(isset ($errors['tags'])) ? 'error' : ''?>"><?=Language::GetText('tags')?>:</label>
        <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" />
        <p class="hint"><?=Language::GetText('comma_delimited')?></p>

        <label class="<?=(isset ($errors['cat_id'])) ? 'error' : ''?>"><?=Language::GetText('category')?>:</label>
        <select class="dropdown" name="cat_id">
        <?php while ($cat = $db->FetchObj ($result_cat)): ?>
            <option value="<?=$cat->cat_id?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $cat->cat_id) || (!isset ($data['cat_id']) && $video->cat_id == $cat->cat_id) ? ' selected="selected"' : ''?>><?=$cat->cat_name?></option>
        <?php endwhile; ?>
        </select>

        <label class="<?=(isset ($errors['description'])) ? 'error' : ''?>"><?=Language::GetText('description')?>:</label>
        <textarea class="text" name="description" rows="10" cols="45"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>

        <input id="disable_embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors)) ? ($data['disable_embed'] == '1' ? 'checked="checked"' : '') : ($video->disable_embed == '1' ? 'checked="checked"' : '')?> />
        <label for="disable_embed"><?=Language::GetText('disable_embed')?></label> <em>(<?=Language::GetText('disable_embed_description')?>)</em><br>

        <input id="gated_video" type="checkbox" name="gated" value="1" <?=(!empty ($errors)) ? ($data['gated'] == '1' ? 'checked="checked"' : '') : ($video->gated == '1' ? 'checked="checked"' : '')?> />
        <label for="gated_video"><?=Language::GetText('gated')?></label> <em>(<?=Language::GetText('gated_description')?>)</em><br>

        <input id="private_video" data-block="private_url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors)) ? ($data['private'] == '1' ? 'checked="checked"' : '') : ($video->private == '1' ? 'checked="checked"' : '')?> />
        <label for="private_video"><?=Language::GetText('private')?></label> <em>(<?=Language::GetText('private_description')?>)</em><br>

        <p id="private_url" class="<?=(!empty ($errors)) ? ($data['private'] == '1' ? '' : 'hide') : ($video->private == '1' ? '' : 'hide')?>">
            <label class="<?=(isset ($errors['private_url'])) ? 'error' : ''?>"><?=Language::GetText('private_url')?>:</label>
            <?=HOST?>/private/videos/<span><?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?></span>/

            <input type="hidden" name="private_url" value="<?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?>" />
            <a href="" class="small"><?=Language::GetText('regenerate')?></a>
        </p>

        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('update_video_button')?>" />
    </form>

</div>

<?php View::Footer(); ?>