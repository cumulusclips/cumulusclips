<?php

View::SetLayout('myaccount');
View::Header();

?>

<div id="myaccount">
    <p class="large"><?=Language::GetText('myaccount_header')?> - <?=$user->username?></p>

    <div class="left">
        <div>
            <div class="avatar"><span><img alt="<?=$user->username?>" src="<?=$user->getAvatarUrl()?>" /></span></div>
            <a href="<?=HOST?>/myaccount/profile/#update_avatar"><?=Language::GetText('edit_avatar')?></a>
        </div>
        <p><strong><?=Language::GetText('joined')?>:</strong> <?=Functions::DateFormat('m/d/Y',$user->date_created)?></p>
        <p><strong><?=Language::GetText('last_login')?>:</strong> <?=Functions::DateFormat('m/d/Y',$user->last_login)?></p>
        <p><strong><?=Language::GetText('profile_views')?>:</strong> <?=$user->views?></p>
    </div>

    <div class="right">
        <p><span><?=Language::GetText('first_name')?></span> <?=empty($user->first_name) ? '-' : $user->first_name?></p>
        <p><span><?=Language::GetText('last_name')?></span> <?=empty($user->last_name) ? '-' : $user->last_name?></p>
        <p><span><?=Language::GetText('email')?></span> <?=$user->email?></p>
        <p><span><?=Language::GetText('website')?></span> <?=empty($user->website) ? '-' : $user->website?></p>
        <p><span><?=Language::GetText('about_me')?></span> <?=empty($user->about_me) ? '-' : $user->about_me?></p>
        <p><a href="<?=HOST?>/myaccount/profile/" title="<?=Language::GetText('update_profile')?>"><?=Language::GetText('update_profile')?></a></p>
    </div>
</div>


<?php View::Footer(); ?>