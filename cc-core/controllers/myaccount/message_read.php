<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Message');


// Establish page variables, objects, arrays, etc
View::InitView ('message_read');
Plugin::Trigger ('message_read.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);



### Verify a message was chosen
if (empty ($_GET['msg']) || !is_numeric ($_GET['msg'])) {
    App::Throw404();
}



### Retrieve message information
$message_id = trim ($_GET['msg']);
$data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $message_id);
$message_id = Message::Exist ($data);
if ($message_id) {
    View::$vars->message = new Message ($message_id);
    $data = array ('status' => 'read');
    View::$vars->message->Update ($data);
} else {
    App::Throw404();
}


// Outuput page
Plugin::Trigger ('message_read.before_render');
View::Render ('myaccount/message_read.tpl');

?>