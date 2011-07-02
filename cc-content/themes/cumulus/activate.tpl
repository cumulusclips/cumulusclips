<?php View::Header(); ?>

<h1><?=Language::GetText('activate_header')?></h1>

<?php if ($success): ?>
    <div id="success"><?=$success?></div>
<?php elseif ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php endif; ?>

<?php View::Footer(); ?>