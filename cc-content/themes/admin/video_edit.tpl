<div id="video-edit">

    <h1><?=Language::GetText('update_video_header')?></h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>"><?=Language::GetText('back_to_videos')?></a></p>

        <form action="<?=ADMIN?>/video_edit.php?id=<?=$video->video_id?>" method="post">

            <div class="row<?=(isset ($Errors['title'])) ? ' errors' : ''?>">
                <label><?=Language::GetText('title')?>:</label>
                <input class="text" type="text" name="title" value="<?=(!empty ($Errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />
            </div>

            <div class="row<?=(isset ($Errors['description'])) ? ' errors' : ''?>">
                <label><?=Language::GetText('description')?>:</label>
                <textarea class="text" name="description"><?=(!empty ($Errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>
            </div>

            <div class="row<?=(isset ($Errors['tags'])) ? ' errors' : ''?>">
                <label><?=Language::GetText('tags')?>:</label>
                <input class="text" type="text" name="tags" value="<?=(!empty ($Errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" /> <em>*<?=Language::GetText('comma_delimited')?></em>
            </div>

            <div class="row<?=(isset ($Errors['cat_id'])) ? ' errors' : ''?>">
                <label><?=Language::GetText('category')?>:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $cat_id) || (!isset ($data['cat_id']) && $video->cat_id == $cat_id) ? ' selected="selected"' : ''?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="<?=Language::GetText('update_video_button')?>" />
            </div>
        </form>

    </div>


</div>