<?php

$obj = new stdClass();
$obj->enabled = '0';
$obj->host = '';
$obj->port = 25;
$obj->username = '';
$obj->password = '';
echo serialize($obj);

//include_once (dirname (__FILE__) . '/cc-core/controllers/index.php');
?>