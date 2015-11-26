<?php

$this->view->options->disableView = true;
header("Content-Type: text/css");
$css = Settings::get('custom_css');
echo Plugin::triggerFilter('css.system', $css);