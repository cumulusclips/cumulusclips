<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_complete_header')?></h1>
<div class="block">
    <p><?=Language::GetText('upload_complete_text', array ('sitename' => $config->sitename))?></p>
</div>

<?php View::Footer(); ?>