<?php if ($message): ?>
<div id="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<h1><?=$header?></h1>
<?php if ($sub_header): ?>
<h3><?=$sub_header?></h3>
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
        <tr>
            <td class="large">Member</td>
            <td class="large">Email</td>
            <td class="large">Date Joined</td>
        </tr>
    </table>
</div>

<div class="block">
    <table class="list">
        <?php while ($row = $db->FetchObj ($result)): ?>
            <?php $user = new User ($row->user_id); ?>
            <tr>
                <td>
                    <a href="" class="large"><?=$user->username?></a>
                    <div class="record-actions">
                        <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                        <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->user_id?>">Edit</a>
                        <a class="delete" href="<?=$pagination->GetURL('delete='.$user->user_id)?>">Delete</a>
                    </div>
                </td>
                <td><?=$user->email?></td>
                <td><?=$user->date_joined?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?=$pagination->paginate()?>