<?php

### Created on June 10, 2009
### Created by Miguel A. Hurtado
### This script unsubscribes users from all non-account mailings


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Privacy');
View::InitView();
Plugin::Trigger ('opt_out.start');


// Establish page variables, objects, arrays, etc
View::LoadPage ('opt_out');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);




### Verify user actually unsubscribed
if (isset ($_GET['email'])) {

    $email = $db->Escape ($_GET['email']);
    $data = array ('email' => $email);
    $id = User::Exist ($data);
    if ($id) {
        $privacy = new Privacy ($id);
        $data = array (
            'new_video'         => 'no',
            'new_message'       => 'no',
            'newsletter'        => 'no',
            'video_comment'     => 'no',
            'channel_comment'   => 'no'
        );
        $privacy->Update ($data);
    } else {
        App::Throw404();
    }

} else {
    App::Throw404();
}


// Output Page
Plugin::Trigger ('opt_out.pre_render');
View::Render ('opt_out.tpl');

?>