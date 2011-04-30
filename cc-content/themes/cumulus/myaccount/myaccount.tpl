
<p class="large"><?=Language::GetText('myaccount_header')?> - <?=$user->username?></p>
<div class="block">

    <p>
        <img width="100" height="100" src="<?=$user->avatar?>" /><br />
        <a href="<?php echo HOST; ?>/myaccount/profile/" title="<?=Language::GetText('edit_picture')?>"><?=Language::GetText('edit_picture')?></a>
    </p>
    <br />
    <p><strong><?=Language::GetText('member_since')?>:</strong> <?php echo $user->date_joined; ?></p>
    <p><strong><?=Language::GetText('last_login')?>:</strong> <?php echo $user->last_login; ?></p>
    <p><strong><?=Language::GetText('profile_views')?>:</strong> <?php echo $user->views; ?></p>

</div>
