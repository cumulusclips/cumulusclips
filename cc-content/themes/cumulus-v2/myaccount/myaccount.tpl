<?php $view->SetLayout ('myaccount'); ?>

<div id="myaccount">
    <p class="large"><?=Language::GetText('myaccount_header')?> - <?=$loggedInUser->username?></p>

    <div class="left">
        <div>
            <div class="avatar"><span><img alt="<?=$loggedInUser->username?>" src="<?=$view->getService('User')->getAvatarUrl($loggedInUser)?>" /></span></div>
            <a href="<?=HOST?>/myaccount/profile/#update_avatar"><?=Language::GetText('edit_avatar')?></a>
        </div>
        <p><strong><?=Language::GetText('joined')?>:</strong> <?=Functions::DateFormat('m/d/Y',$loggedInUser->dateCreated)?></p>
        <p><strong><?=Language::GetText('last_login')?>:</strong> <?=Functions::DateFormat('m/d/Y',$loggedInUser->lastLogin)?></p>
        <p><strong><?=Language::GetText('profile_views')?>:</strong> <?=$loggedInUser->views?></p>
    </div>

    <div class="right">
        <p><span><?=Language::GetText('first_name')?></span> <?=empty($loggedInUser->firstName) ? '-' : $loggedInUser->firstName?></p>
        <p><span><?=Language::GetText('last_name')?></span> <?=empty($loggedInUser->lastName) ? '-' : $loggedInUser->lastName?></p>
        <p><span><?=Language::GetText('email')?></span> <?=$loggedInUser->email?></p>
        <p><span><?=Language::GetText('website')?></span> <?=empty($loggedInUser->website) ? '-' : $loggedInUser->website?></p>
        <p><span><?=Language::GetText('about_me')?></span> <?=empty($loggedInUser->aboutMe) ? '-' : $loggedInUser->aboutMe?></p>
        <p><a href="<?=HOST?>/myaccount/profile/" title="<?=Language::GetText('update_profile')?>"><?=Language::GetText('update_profile')?></a></p>
    </div>
</div>