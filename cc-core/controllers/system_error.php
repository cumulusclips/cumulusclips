<?php

// Establish page variables, objects, arrays, etc
View::InitView('system_error');
Plugin::Trigger('system_error.start');

View::$vars->logged_in = UserService::LoginCheck();
if (View::$vars->logged_in) {
    $userMapper = new UserMapper();
    $userMapper->getUserById(View::$vars->logged_in);
}

// Output Page
Plugin::Trigger('system_error.before_render');
View::Render('system_error.tpl');