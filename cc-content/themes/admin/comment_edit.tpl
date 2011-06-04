<div id="comment-edit">

    <h1>Update Comment</h1>
    
    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>

    
    <div class="block">

        <p><a href="<?=$list_page?>">Go back to comments</a></p>

        <form action="<?=ADMIN?>/comment_edit.php?id=<?=$comment->comment_id?>" method="post">

            <?php if ($comment->user_id == 0): ?>

                <div class="row<?=(isset ($Errors['name'])) ? ' errors' : ''?>">
                    <label><?=Language::GetText('name')?>:</label>
                    <input class="text" type="text" name="name" value="<?=(isset ($data['name'])) ? $data['name'] : $comment->name?>" />
                </div>

                <div class="row<?=(isset ($Errors['email'])) ? ' errors' : ''?>">
                    <label>*<?=Language::GetText('email')?>:</label>
                    <input class="text" type="text" name="email" value="<?=(isset ($data['email'])) ? $data['email'] : $comment->email?>" />
                </div>

                <div class="row<?=(isset ($Errors['website'])) ? ' errors' : ''?>">
                    <label><?=Language::GetText('website')?>:</label>
                    <input class="text" type="text" name="website" value="<?=(isset ($data['website'])) ? $data['website'] : $comment->website?>" />
                </div>

            <?php else: ?>
            
                <div class="row">
                    <label><?=Language::GetText('username')?>:</label>
                    <a href="<?=$comment->website?>"><?=$comment->name?></a>
                </div>

            <?php endif; ?>

            <div class="row">
                <label><?=Language::GetText('date_posted')?>:</label>
                <?=$comment->date_created?>
            </div>

            <div class="row">
                <label>In Response To:</label>
                <a href="<?=HOST?>/videos/<?=$comment->video_id?>/<?=$video->slug?>/"><?=$video->title?></a>
            </div>

            <div class="row<?=(isset ($Errors['status'])) ? ' errors' : ''?>">
                <label>Status:</label>
                <select name="status" class="dropdown">
                    <option value="approved"<?=($comment->status == 'approved')?' selected="selected"':''?>>Approved</option>
                    <option value="pending"<?=($comment->status == 'pending')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=($comment->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row<?=(isset ($Errors['comment'])) ? ' errors' : ''?>">
                <label><?=Language::GetText('comments')?>:</label>
                <textarea rows="7" cols="55" class="text" name="comments"><?=(isset ($data['comments'])) ? $data['comments'] : $comment->comments?></textarea>
            </div>

            <div class="row-shift">
                <input type="hidden" value="yes" name="submitted" />
                <input type="submit" class="button" value="Update Comment" />
            </div>

        </form>

    </div>

</div>