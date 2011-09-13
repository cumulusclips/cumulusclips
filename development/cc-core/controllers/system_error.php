<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('system_error');
Plugin::Trigger ('system_error.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in)  View::$vars->user = new User (View::$vars->logged_in);



// Output Page
Plugin::Trigger ('system_error.before_render');
View::Render ('system_error.tpl');

?>