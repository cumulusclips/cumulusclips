<?php

### Created on June 10, 2009
### Created by Miguel A. Hurtado
### This script unsubscribes users from all non-account mailings


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Privacy');


// Establish page variables, objects, arrays, etc
View::InitView ('opt_out');
Plugin::Trigger ('opt_out.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);




### Verify user actually unsubscribed
if (isset ($_GET['email'])) {

    $data = array ('email' => $email);
    $id = User::Exist ($data);
    if ($id) {
        $privacy = Privacy::LoadByUser ($id);
        $data = array (
            'new_video'         => 'no',
            'new_message'       => 'no',
            'newsletter'        => 'no',
            'video_comment'     => 'no',
            'channel_comment'   => 'no'
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

?>