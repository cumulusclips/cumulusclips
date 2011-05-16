<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.index.start');
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
$page_title = 'Admin Panel';
$content = 'index.tpl';


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>