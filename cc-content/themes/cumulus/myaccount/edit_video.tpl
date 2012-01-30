<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('update_video_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="block">

    <p><a href="<?=HOST?>/myaccount/myvideos/"><?=Language::GetText('back_to_videos')?></a></p><br />
    <form action="<?=HOST?>/myaccount/editvideo/<?=$video->video_id?>/" method="post">

        <div class="row<?=(isset ($errors['title'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('title')?>:</label>
            <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />
        </div>

        <div class="row<?=(isset ($errors['tags'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('tags')?>:</label>
            <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" /> (<?=Language::GetText('comma_delimited')?>)
        </div>

        <div class="row<?=(isset ($errors['description'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('description')?>:</label>
            <textarea class="text" name="description" rows="10" cols="45"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>
        </div>

        <div class="row<?=(isset ($errors['cat_id'])) ? ' errors' : ''?>">
            <label><?=Language::GetText('category')?>:</label>
            <select class="dropdown" name="cat_id">
            <?php while ($cat = $db->FetchObj ($result_cat)): ?>
                <option value="<?=$cat->cat_id?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $cat->cat_id) || (!isset ($data['cat_id']) && $video->cat_id == $cat->cat_id) ? ' selected="selected"' : ''?>><?=$cat->cat_name?></option>
            <?php endwhile; ?>
            </select>
        </div>
        
        <div class="row-shift">
            <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors)) ? ($data['disable_embed'] == '1' ? 'checked="checked"' : '') : ($video->disable_embed == '1' ? 'checked="checked"' : '')?> />
            <label for="disable-embed"><?=Language::GetText('disable_embed')?></label> <em>(<?=Language::GetText('disable_embed_description')?>)</em>
        </div>

        <div class="row-shift">
            <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty ($errors)) ? ($data['gated'] == '1' ? 'checked="checked"' : '') : ($video->gated == '1' ? 'checked="checked"' : '')?> />
            <label for="gated-video"><?=Language::GetText('gated')?></label> <em>(<?=Language::GetText('gated_description')?>)</em>
        </div>

        <div class="row-shift">
            <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors)) ? ($data['private'] == '1' ? 'checked="checked"' : '') : ($video->private == '1' ? 'checked="checked"' : '')?> />
            <label for="private-video"><?=Language::GetText('private')?></label> <em>(<?=Language::GetText('private_description')?>)</em>
        </div>

        <div id="private-url" class="row <?=(!empty ($errors)) ? ($data['private'] == '1' ? '' : 'hide') : ($video->private == '1' ? '' : 'hide')?>">
            
            <label <?=(isset ($errors['private_url'])) ? 'class="errors"' : ''?>><?=Language::GetText('private_url')?>:</label>
            <?=HOST?>/private/videos/<span><?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?></span>/

            <input type="hidden" name="private_url" value="<?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?>" />
            <a href="" class="small"><?=Language::GetText('regenerate')?></a>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('update_video_button')?>" />
        </div>
    </form>

</div>

<?php View::Footer(); ?>