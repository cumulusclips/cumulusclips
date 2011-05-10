<?php

### Created on March 8, 2009
### Created by Miguel A. Hurtado
### This script displays the 404 not found page


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();
Plugin::Trigger ('sys_error.start');


// Establish page variables, objects, arrays, etc
View::LoadPage ('sys_error');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in)  View::$vars->user = new User (View::$vars->logged_in);



// Output Page
Plugin::Trigger ('sys_error.pre_render');
View::Render ('sys_error.tpl');

?>