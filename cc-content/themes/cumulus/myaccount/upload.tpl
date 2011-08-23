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
            <label<?=(isset ($errors['cat_id'])) ? ' class="errors"' : ''?>><?=Language::GetText('category')?>:</label>
            <select class="dropdown" name="cat_id">
            <?php while ($cat = $db->FetchObj ($result_cat)): ?>
                <option<?=(isset ($data['cat_id']) && $cat->cat_id == $data['cat_id']) ? ' selected="selected"' : ''?> value="<?=$cat->cat_id?>"><?=$cat->cat_name?></option>
            <?php endwhile; ?>
            </select>
        </div>

        <div class="row">
            <label<?=(isset ($errors['description'])) ? ' class="errors"' : ''?>><?=Language::GetText('description')?>:</label>
            <textarea class="text" name="description" rows="10" cols="45"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : ''?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('submit_button')?>" />
        </div>
    
    </form>

</div>

<?php View::Footer(); ?>