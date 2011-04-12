<?php

### Created on March 24, 2009
### Created by Miguel A. Hurtado
### This script displays the my account page


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Picture');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck(HOST . '/login/');
View::$vars->page_title = 'Techie Videos - My Account';
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->new_messages = NULL;



// Check for unread messages
$query = "SELECT message_id FROM messages WHERE recipient = " . View::$vars->user->user_id . " AND status = 'unread'";
$result = $db->Query ($query);
if ($db->Count($result) > 0) {
    View::$vars->new_messages = '&nbsp;&nbsp;<strong>*(new messages)</strong>';
}


// Output Page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/myaccount.tpl');

?>