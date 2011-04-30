<?php

### Created on April 19, 2009
### Created by Miguel A. Hurtado
### This script allows users to read their private messages


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Message');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('message_read');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);



### Verify a message was chosen
if (empty ($_GET['msg']) || !is_numeric ($_GET['msg'])) {
    App::Throw404();
}



### Retrieve message information
$message_id = trim ($_GET['msg']);
$data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $message_id);
$id = Message::Exist ($data);
if ($id) {
    View::$vars->message = new Message ($id);
    $data = array ('status' => 'read');
    View::$vars->message->Update ($data);
} else {
    App::Throw404();
}


// Outuput page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/message_read.tpl');

?>