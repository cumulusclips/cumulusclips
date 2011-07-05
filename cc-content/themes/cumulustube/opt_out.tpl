<?php View::Header(); ?>

<h1><?=Language::GetText('opt_out_header')?></h1>
<div class="block"><?=Language::GetText('opt_out_text', array ('link' => HOST . '/myaccount/privacy-settings/'))?></div>

<?php View::Footer(); ?>