<?php

View::SetLayout ('myaccount');
View::Header();

?>

<h1><?=Language::GetText('upload_complete_header')?></h1>
<p><?=Language::GetText('upload_complete_text', array ('sitename' => $config->sitename))?></p>

<?php View::Footer(); ?>