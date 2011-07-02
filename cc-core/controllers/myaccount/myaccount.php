<?php

### Created on March 24, 2009
### Created by Miguel A. Hurtado
### This script displays the my account page


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('myaccount');
Plugin::Trigger ('myaccount.start');
View::$vars->logged_in = User::LoginCheck(HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->new_messages = NULL;
View::$vars->meta->title = Functions::Replace(View::$vars->meta->title, array ('username' => View::$vars->user->username));



// Check for unread messages
$query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE recipient = " . View::$vars->user->user_id . " AND status = 'unread'";
$result = $db->Query ($query);
if ($db->Count($result) > 0) {
    View::$vars->new_messages = '&nbsp;&nbsp;<strong>*(new messages)</strong>';
}


// Output Page
Plugin::Trigger ('myaccount.before_render');
View::Render ('myaccount/myaccount.tpl');

?>