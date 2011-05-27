<div id="video-edit">

    <h1>Edit Video</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/video_edit.php?id=<?=$video->video_id?>" method="post">

            <div class="row<?=(isset ($Errors['title'])) ? ' errors' : ''?>">
                <label>Title:</label>
                <input class="text" type="text" name="title" value="<?=(!empty ($Errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />
            </div>

            <div class="row<?=(isset ($Errors['description'])) ? ' errors' : ''?>">
                <label>Description:</label>
                <textarea rows="5" cols="50" class="text" name="description"><?=(!empty ($Errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>
            </div>

            <div class="row<?=(isset ($Errors['tags'])) ? ' errors' : ''?>">
                <label>Tags:</label>
                <input class="text" type="text" name="tags" value="<?=(!empty ($Errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" />
                <em>*Comma Delimited</em>
            </div>

            <div class="row<?=(isset ($Errors['cat_id'])) ? ' errors' : ''?>">
                <label>Category:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $_key => $_value): ?>
                    <option value="<?=$_key?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $_key) || (!isset ($data['cat_id']) && $video->cat_id == $_key) ? ' selected="selected"' : ''?>><?=$_value?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Video" />
            </div>
        </form>

    </div>


</div>