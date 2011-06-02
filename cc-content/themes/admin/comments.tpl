<div id="comments">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>

    
    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">
        <div class="jump">
            Jump To:
            <select id="comment-status-select" name="status" onChange="window.location='<?=ADMIN?>/comments.php?status='+this.value;">
                <option <?=(isset($status) && $status == 'approved') ? 'selected="selected"' : ''?>value="approved">Approved</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/comments.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>
    </div>

    <?php if ($total > 0): ?>

        <div class="block list">
            <table>
                <thead>
                    <tr>
                        <td class="large">Comments</td>
                        <td class="large">Email</td>
                        <td class="large">Date Posted</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $comment = new Comment ($row->comment_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td><?=$comment->comments?></td>
                        <td>
                            <a href="" class="large"><?=$comment->user_id?></a>
                            <div class="record-actions invisible">
                                <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                                <a href="<?=ADMIN?>/comment_edit.php?id=<?=$comment->comment_id?>">Edit</a>
                                
                                <?php if ($status == 'approved'): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$comment->comment_id)?>">Ban</a>
                                <?php elseif ($status == 'pending'): ?>
                                    <a href="<?=$pagination->GetURL('activate='.$comment->comment_id)?>">Activate</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$comment->comment_id)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$comment->comment_id)?>" data-confirm="You are about to delete this comment, this cannot be undone. Are you sure you want to do this?">Delete</a>
                            </div>
                        </td>
                        <td><?=$comment->date_created?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No members found</strong></div>
    <?php endif; ?>

</div>