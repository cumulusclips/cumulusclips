<link href="/css/admin.css" rel="stylesheet" type="text/css" />

<div class="block-header" id="browse-users-header"><h1>Browse Users</h1></div>
<table class="block-list">
    <tr>
        <th>User ID</th>
        <th class="align-left">Username</th>
        <th>Join Date</th>
        <th>Account Status</th>
        <th>Edit</th>
    </tr>

<?php while ($row = $db->FetchAssoc ($result)): ?>

    <?php $user = new User ($row['user_id'], $db); ?>
    <?php $odd = isset ($odd) ? null : true ?>

    <tr<?=($odd)?' class="odd"':''?>>
        <td><?=$user->user_id?></td>
        <td class="align-left"><a href="<?=HOST?>/channels/<?=$user->username?>/"><?=$user->username?></a></td>
        <td><?=date('m/d/Y',strtotime($user->date_joined))?></td>
        <td><?=$user->account_status?></td>
        <td><a href="<?=HOST?>/admin/users/edit/<?=$user->user_id?>/">Edit</a></td>
    </tr>

<?php endwhile; ?>
</table>
