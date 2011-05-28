<div id="videos">

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
            <select id="video-status-select" name="status" onChange="window.location='<?=ADMIN?>/videos.php?status='+this.value;">
                <option <?=(isset($status) && $status == 1) ? 'selected="selected"' : ''?>value="1">Approved</option>
                <option <?=(isset($status) && $status == 9) ? 'selected="selected"' : ''?>value="9">Pending</option>
                <option <?=(isset($status) && $status == 7) ? 'selected="selected"' : ''?>value="7">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/videos.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>
    </div>
    
    <div class="block list">
        <table>
            <thead>
                <tr>
                    <td class="video-title large">Title</td>
                    <td class="category large">Category</td>
                    <td class="large">Member</td>
                    <td class="large">Upload Date</td>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $db->FetchObj ($result)): ?>

                <?php $odd = empty ($odd) ? true : false; ?>
                <?php $video = new Video ($row->video_id); ?>

                <tr class="<?=$odd ? 'odd' : ''?>">
                    <td class="video-title">
                        <a href="" class="large"><?=$video->title?></a><br />
                        <div class="record-actions invisible">
                            <a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>/">Watch</a>
                            <a href="<?=ADMIN?>/video_edit.php?id=<?=$video->video_id?>">Edit</a>
                            <?php if ($status == 6): ?>
                                <a class="delete" href="<?=$pagination->GetURL('ban='.$video->video_id)?>">Ban</a>
                            <?php elseif ($status == 9): ?>
                                <a href="<?=$pagination->GetURL('approve='.$video->video_id)?>">Approve</a>
                            <?php elseif ($status == 7): ?>
                                <a href="<?=$pagination->GetURL('unban='.$video->video_id)?>">Unban</a>
                            <?php endif; ?>
                            <a class="delete" href="<?=$pagination->GetURL('delete='.$video->video_id)?>">Delete</a>
                        </div>
                    </td>
                    <td class="category"><?=$categories[$video->cat_id]?></td>
                    <td><?=$video->username?></td>
                    <td><?=$video->date_created?></td>
                </tr>
                
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?=$pagination->paginate()?>

</div>