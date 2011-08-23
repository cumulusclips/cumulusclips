<?php View::Header(); ?>

<h1><?=Language::GetText('activate_header')?></h1>

<?php if ($message): ?>
    <div id="message" class="<?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php View::Footer(); ?>