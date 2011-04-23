<h1><?=Language::GetText('activate_header')?></h1>

<?php if ($Success): ?>
    <div id="success"><?=Language::GetText('activate_text_success', array ('link' => HOST . '/myaccount/'))?></div>
<?php elseif ($Error): ?>
    <div id="error"><?=Language::GetText('activate_text_error', array ('link' => HOST . '/login/forgot/'))?></div>
<?php endif; ?>


