<?php

View::SetLayout ('myaccount');
View::Header();

?>

<p class="large"><?=Language::GetText('myaccount_header')?> - <?=$user->username?></p>

<div id="message"></div>

<div class="block">

    <div id="myaccount-left">
        <div>
            <div class="avatar"><span><img alt="<?=$user->username?>" src="<?=$user->avatar_url?>" /></span></div>
            <a href="<?=HOST?>/myaccount/profile/#update-avatar"><?=Language::GetText('edit_avatar')?></a>
        </div>
        <br />
        <p><strong><?=Language::GetText('joined')?>:</strong> <?=Functions::DateFormat('m/d/Y',$user->date_created)?></p>
        <p><strong><?=Language::GetText('last_login')?>:</strong> <?=Functions::DateFormat('m/d/Y',$user->last_login)?></p>
        <p><strong><?=Language::GetText('profile_views')?>:</strong> <?=$user->views?></p>
    </div>



    <form id="status-form">
        <p class="big"><?=Language::GetText('update_status')?></p>
        <textarea class="text" name="post"></textarea><br />
        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button-small" type="submit" name="button" value="<?=Language::GetText('post_update_button')?>" />
    </form>

    <div class="clear"></div>

</div>

<?php View::Footer(); ?>