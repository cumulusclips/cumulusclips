<?php

// Establish page variables, objects, arrays, etc
View::InitView ('opt_out');
Plugin::Trigger ('opt_out.start');

View::$vars->logged_in = UserService::LoginCheck();
if (View::$vars->logged_in) {
    $userMapper = new UserMapper();
    $userMapper->getUserById(View::$vars->logged_in);
}

### Verify user actually unsubscribed
if (isset ($_GET['email'])) {

    $data = array ('email' => $_GET['email']);
    $id = User::Exist ($data);
    if ($id) {
        $privacy = Privacy::LoadByUser ($id);
        $data = array (
            'new_video'         => 'no',
            'new_message'       => 'no',
            'video_comment'     => 'no'
        );
        Plugin::Trigger ('opt_out.opt_out');
        $privacy->Update ($data);
    } else {
        App::Throw404();
    }

} else {
    App::Throw404();
}


// Output Page
Plugin::Trigger ('opt_out.before_render');
View::Render ('opt_out.tpl');