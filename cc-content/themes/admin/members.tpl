<h1><?=$header?> Members:</h1>

<select id="member-status-select" name="status" onChange="window.location='<?=ADMIN?>/members.php?status='+this.value;">
    <option <?=(isset($status) && $status == 'active') ? 'selected="selected"' : ''?>value="active">Active</option>
    <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
    <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
</select>

<form method="POST" action="<?=ADMIN?>/members.php">
    <input type="hidden" name="search_submitted" value="true" />
    <input type="text" name="username" value="" />&nbsp;
    <input type="submit" name="search" value="Search" />
</form>


<div>
    <?php while ($row = $db->FetchObj ($result)): ?>
        <?php $user = new User ($row->user_id); ?>
        <p>
            <?=$user->username?>
            <br /><?=$user->email?>
            <br /><?=$user->account_status?>
            <br /><a href="<?=ADMIN?>/members/edit/<?=$user->user_id?>/">Edit</a> &nbsp; | &nbsp;
            <a href="<?=$pagination->GetURL('delete='.$user->user_id)?>">Delete</a>
        </p>
    <?php endwhile; ?>
</div>

<?=$pagination->paginate()?>