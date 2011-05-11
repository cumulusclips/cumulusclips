<?php

include ('cc-core/config/bootstrap.php');
App::LoadClass ('Privacy');

$privacy = Privacy::LoadByUser (18);
echo '<pre>',print_r($privacy,true),'</pre>';

?>