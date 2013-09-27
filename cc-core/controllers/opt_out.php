<?php

// Establish page variables, objects, arrays, etc
View::InitView('opt_out');
Plugin::triggerEvent('opt_out.start');

View::$vars->logged_in = UserService::LoginCheck();
$userMapper = new UserMapper();
if (View::$vars->logged_in) {
    $userMapper->getUserById(View::$vars->logged_in);
}

### Verify user actually unsubscribed
if (isset($_GET['email'])) {

    $user = $userMapper->getUserByCustom(array('email' => $_GET['email']));
    if ($user) {
        $privacyMapper = new PrivacyMapper();
        $privacy = Privacy::getPrivacyByUser($user->userId);
        $privacy->newVideo = false;
        $privacy->newMessage = false;
        $privacy->videoComment = false;
        $privacyMapper->save($privacy);
        Plugin::triggerEvent('opt_out.opt_out');
    } else {
        App::Throw404();
    }

} else {
    App::Throw404();
}

// Output Page
Plugin::triggerEvent('opt_out.before_render');
View::Render ('opt_out.tpl');