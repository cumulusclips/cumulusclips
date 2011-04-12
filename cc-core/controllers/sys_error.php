<?php

### Created on March 8, 2009
### Created by Miguel A. Hurtado
### This script displays the 404 not found page


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in)  View::$vars->user = new User (View::$vars->logged_in);
View::$vars->page_title = 'Techie Videos - System Error';



// Output Page
View::Render ('sys_error.tpl');

?>