<?php

### Created on March 8, 2009
### Created by Miguel A. Hurtado
### This script displays the 404 not found page


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('system_404');
Plugin::Trigger ('system_404.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);



header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');
View::Render ('system_404.tpl');

?>