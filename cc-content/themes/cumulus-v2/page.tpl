<?php

View::SetLayout ($page->layout);
View::Header();

?>

<h1><?=$page->title?></h1>

<div class="block"><?=$page->content?></div>

<?php View::Footer(); ?>