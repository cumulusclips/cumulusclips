<?php

### Created on June 10, 2009
### Created by Miguel A. Hurtado
### This script unsubscribes users from all non-account mailings


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Login');
App::LoadClass ('User');
App::LoadClass ('Privacy');


// Establish page variables, objects, arrays, etc
$login = new Login ($db);
$logged_in = $login->LoginCheck();
$page_title = 'Techie Videos - Email Opt-Out';



// Retrieve user data if logged in
if ($logged_in) {
	$user = new User ($logged_in, $db);
}



### Verify user actually unsubscribed
if (isset ($_GET['email'])) {

    $email = $db->Escape ($_GET['email']);
    $data = array ('email' => $email);
    $id = User::Exist ($data, $db);
    if ($id) {
        $privacy = new Privacy ($id, $db);
        $data = array (
            'new_video'         => 'no',
            'new_message'       => 'no',
            'newsletter'        => 'no',
            'video_comment'     => 'no',
            'channel_comment'   => 'no'
        );
        $privacy->Update ($data);
    } else {
        Login::Forward ('/');
    }

} else {
    Login::Forward ('/');
}

$content_file = 'opt_out.tpl';
include (THEMES . '/layouts/two_column.layout.tpl');

?>