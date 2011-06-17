<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('page');
Plugin::Trigger ('page.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) $user = new User (View::$vars->logged_in);
$page_id = null;



### Parse the URI request
$request = preg_replace ('/^\/?(.*?)\/?$/', '$1', $_SERVER['REQUEST_URI']);
$page_id = Page::Exist (array ('slug' => $request));
if ($page_id) {
    View::$vars->page = new Page ($page_id);
} else {
    echo '404';
    exit();
//    App::Throw404();
}


// Output Page
Plugin::Trigger ('page.before_render');
View::Render ('page.tpl');

?>