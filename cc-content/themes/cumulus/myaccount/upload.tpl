<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="block">
    
    <form action="<?=HOST?>/myaccount/upload/" method="post">
        
        <div class="row">
            <label<?=(isset ($errors['title'])) ? ' class="errors"' : ''?>><?=Language::GetText('title')?>:</label>
            <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($data['title'])) ? $data['title'] : ''?>" />
        </div>

        <div class="row">
            <label<?=(isset ($errors['tags'])) ? ' class="errors"' : ''?>><?=Language::GetText('tags')?>:</label>
            <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($data['tags'])) ? $data['tags'] : ''?>" />
            <em>(<?=Language::GetText('comma_delimited')?>)</em>
        </div>

        <div class="row">
            <label<?=(isset ($errors['description'])) ? ' class="errors"' : ''?>><?=Language::GetText('description')?>:</label>
            <textarea class="text" name="description" rows="10" cols="45"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : ''?></textarea>
        </div>

        <div class="row">
            <label<?=(isset ($errors['cat_id'])) ? ' class="errors"' : ''?>><?=Language::GetText('category')?>:</label>
            <select class="dropdown" name="cat_id">
            <?php while ($cat = $db->FetchObj ($result_cat)): ?>
                <option<?=(isset ($data['cat_id']) && $cat->cat_id == $data['cat_id']) ? ' selected="selected"' : ''?> value="<?=$cat->cat_id?>"><?=$cat->cat_name?></option>
            <?php endwhile; ?>
            </select>
        </div>

        <div class="row-shift">
            <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors) && !empty ($data['disable_embed'])) ? 'checked="checked"' : ''?> />
            <label for="disable-embed"><?=Language::GetText('disable_embed')?></label> <em>(<?=Language::GetText('disable_embed_description')?>)</em>
        </div>

        <div class="row-shift">
            <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty ($errors) && !empty ($data['gated'])) ? 'checked="checked"' : ''?> />
            <label for="gated-video"><?=Language::GetText('gated')?></label> <em>(<?=Language::GetText('gated_description')?>)</em>
        </div>

        <div class="row-shift">
            <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors) && !empty ($data['private'])) ? 'checked="checked"' : ''?> />
            <label for="private-video"><?=Language::GetText('private')?></label> <em>(<?=Language::GetText('private_description')?>)</em>
        </div>

        <div id="private-url" class="row <?=(!empty ($errors) && !empty ($data['private'])) ? '' : 'hide'?>">
            <label <?=(isset ($errors['private_url'])) ? 'class="errors"' : ''?>><?=Language::GetText('private_url')?>:</label>
            <?=HOST?>/private/videos/<span><?=$private_url?></span>/
            <input type="hidden" name="private_url" value="<?=$private_url?>" />
            <a href="" class="small"><?=Language::GetText('regenerate')?></a>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('submit_button')?>" />
        </div>
    
    </form>

</div>

<?php View::Footer(); ?>