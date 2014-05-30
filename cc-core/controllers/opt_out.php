<?php

Plugin::triggerEvent('opt_out.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

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

Plugin::triggerEvent('opt_out.before_render');