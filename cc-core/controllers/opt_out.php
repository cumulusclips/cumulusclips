<?php

Plugin::triggerEvent('opt_out.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

$userMapper = new UserMapper();

// Verify user actually unsubscribed
if (isset($_GET['email'])) {

    $user = $userMapper->getUserByCustom(array('email' => $_GET['email']));
    if ($user) {
        $privacyMapper = new PrivacyMapper();
        $privacy = $privacyMapper->getPrivacyByUser($user->userId);
        $privacy->newVideo = false;
        $privacy->newMessage = false;
        $privacy->videoComment = false;
        $privacy->commentReply = false;
        $privacyMapper->save($privacy);
    } else {
        App::throw404();
    }

} else {
    App::throw404();
}

Plugin::triggerEvent('opt_out.end');