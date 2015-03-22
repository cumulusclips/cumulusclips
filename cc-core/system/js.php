<?php

$this->view->options->disableView = true;
header("Content-Type: text/javascript");
$js = Settings::get('custom_js');
echo Plugin::triggerFilter('js.system', $js);