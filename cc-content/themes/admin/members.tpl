<div id="members">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>

    
    <?php if ($message): ?>
    <div id="<?=$message_type?>"><?=$message?></div>
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
                <input type="submit" name="submit" value="Search" />
            </form>
        </div>
    </div>


    <div class="list-headers">
        <table class="list">
        </table>
    </div>

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
                            <a class="delete" href="<?=$pagination->GetURL('delete='.$user->user_id)?>">Delete</a>
                        </div>
                    </td>
                    <td><?=$user->email?></td>
                    <td><?=$user->date_joined?></td>
                </tr>
                
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?=$pagination->paginate()?>

</div>