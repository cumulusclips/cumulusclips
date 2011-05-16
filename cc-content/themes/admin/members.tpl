<h1>Browse <?=$status?> Members</h1>

<select id="member-status-select" name="status">
    <option value="Active">Active</option>
    <option value="Pending">Pending</option>
    <option value="Banned">Banned</option>
</select>

<form method="POST">
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
            <br /><a href="<?=ADMIN?>/members_edit.php?id=<?=$user->user_id?>">Edit</a> &nbsp; | &nbsp;
            <a href="<?=$pagination->BuildURL()?>&action=delete&id=<?=$user->user_id?>">Delete</a>
        </p>
    <?php endwhile; ?>
</div>

<?=$pagination->paginate()?>