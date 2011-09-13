<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');

// Establish page variables, objects, arrays, etc
View::InitView ('system_404');
Plugin::Trigger ('system_404.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);


// Output page
header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');
View::Render ('system_404.tpl');

?>