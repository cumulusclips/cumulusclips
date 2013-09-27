<?php

// Establish page variables, objects, arrays, etc
View::InitView ('system_404');
Plugin::Trigger ('system_404.start');

View::$vars->logged_in = UserService::LoginCheck();
if (View::$vars->logged_in) {
    $userMapper = new UserMapper();
    $userMapper->getUserById(View::$vars->logged_in);
}

// Output page
header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');
View::Render ('system_404.tpl');