<?php

// Establish page variables, objects, arrays, etc
View::InitView ('upload_complete');
Plugin::Trigger ('upload_complete.start');
Functions::RedirectIf (View::$vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
App::EnableUploadsCheck();
View::$vars->user = new User (View::$vars->logged_in);



### Verify user completed upload process
if (isset ($_SESSION['upload'])) {
    unset ($_SESSION['upload']);
} else {
    header ('Location: ' . HOST . '/myaccount/upload/video/');
    exit();
}


// Output page
Plugin::Trigger ('upload_complete.before_render');
View::Render ('myaccount/upload_complete.tpl');