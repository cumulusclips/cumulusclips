<div id="members">

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
            <select id="member-status-select" name="status" onChange="window.location='<?=ADMIN?>/members.php?status='+this.value;">
                <option <?=(isset($status) && $status == 'active') ? 'selected="selected"' : ''?>value="active">Active</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/members.php?status=<?=$status?>">
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
                        <td class="large">Member</td>
                        <td class="large">Email</td>
                        <td class="large">Date Joined</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $user = new User ($row->user_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>
                            <a href="" class="large"><?=$user->username?></a>
                            <div class="record-actions invisible">
                                <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                                <a href="<?=ADMIN?>/member_edit.php?id=<?=$user->user_id?>">Edit</a>
                                
                                <?php if ($status == 'active'): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$user->user_id)?>">Ban</a>
                                <?php elseif ($status == 'pending'): ?>
                                    <a href="<?=$pagination->GetURL('activate='.$user->user_id)?>">Activate</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$user->user_id)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$user->user_id)?>" data-confirm="You are about to delete this member and their content, this cannot be undone. Are you sure you want to do this?">Delete</a>
                            </div>
                        </td>
                        <td><?=$user->email?></td>
                        <td><?=$user->date_created?></td>
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