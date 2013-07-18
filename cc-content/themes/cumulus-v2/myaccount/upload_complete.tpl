<?php View::SetLayout ('myaccount'); ?>

<h1><?=Language::GetText('upload_complete_header')?></h1>
<p><?=Language::GetText('upload_complete_text', array ('sitename' => $config->sitename))?></p>