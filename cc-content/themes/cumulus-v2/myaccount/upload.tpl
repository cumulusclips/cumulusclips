<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="form wide">
    
    <form action="<?=HOST?>/myaccount/upload/" method="post">
        <label class="<?=(isset ($errors['title'])) ? 'error' : ''?>"><?=Language::GetText('title')?>:</label>
        <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($data['title'])) ? $data['title'] : ''?>" />

        <label class="<?=(isset ($errors['tags'])) ? 'error' : ''?>"><?=Language::GetText('tags')?>:</label>
        <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($data['tags'])) ? $data['tags'] : ''?>" />
        <p class="hint"><?=Language::GetText('comma_delimited')?></p>

        <label class="<?=(isset ($errors['cat_id'])) ? 'error' : ''?>"><?=Language::GetText('category')?>:</label>
        <select class="dropdown" name="cat_id">
        <?php while ($cat = $db->FetchObj ($result_cat)): ?>
            <option<?=(isset ($data['cat_id']) && $cat->cat_id == $data['cat_id']) ? ' selected="selected"' : ''?> value="<?=$cat->cat_id?>"><?=$cat->cat_name?></option>
        <?php endwhile; ?>
        </select>

        <label class="<?=(isset ($errors['description'])) ? 'error' : ''?>"><?=Language::GetText('description')?>:</label>
        <textarea class="text" name="description" rows="10" cols="45"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : ''?></textarea>

        <input id="disable_embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors) && !empty ($data['disable_embed'])) ? 'checked="checked"' : ''?> />
        <label for="disable_embed"><?=Language::GetText('disable_embed')?></label> <em>(<?=Language::GetText('disable_embed_description')?>)</em><br>

        <input id="gated_video" type="checkbox" name="gated" value="1" <?=(!empty ($errors) && !empty ($data['gated'])) ? 'checked="checked"' : ''?> />
        <label for="gated_video"><?=Language::GetText('gated')?></label> <em>(<?=Language::GetText('gated_description')?>)</em><br>

        <input id="private_video" data-block="private_url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors) && !empty ($data['private'])) ? 'checked="checked"' : ''?> />
        <label for="private_video"><?=Language::GetText('private')?></label> <em>(<?=Language::GetText('private_description')?>)</em><br>
            
        <p id="private_url" class="<?=(!empty ($errors) && !empty ($data['private'])) ? '' : 'hide'?>">
            <label class="<?=(isset ($errors['private_url'])) ? 'error' : ''?>"><?=Language::GetText('private_url')?>:</label>
            <?=HOST?>/private/videos/<span><?=$private_url?></span>/
            <input type="hidden" name="private_url" value="<?=$private_url?>" />
            <a href="" class="small"><?=Language::GetText('regenerate')?></a>
        </p>

        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('submit_button')?>" />
    </form>

</div>

<?php View::Footer(); ?>