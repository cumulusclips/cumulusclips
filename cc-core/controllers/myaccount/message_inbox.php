<?php

### Created on April 9, 2009
### Created by Miguel A. Hurtado
### This script allows users to view their inbox


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Message');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
View::InitView ('message_inbox');
Plugin::Trigger ('message_inbox.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 20;
$url = HOST . '/myaccount/message/inbox';
View::$vars->message = null;





/***********************
Handle form if submitted
***********************/

// Delete message (Request came from this page)
if (isset ($_POST['submitted'])) {

    // Verify messages were chosen
    if (!empty ($_POST['delete']) && is_array ($_POST['delete'])) {

        foreach($_POST['delete'] as $value){
            $data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $value);
            $message_id = Message::Exist ($data);
            if ($message_id) {
                Message::Delete ($message_id);
                Plugin::Trigger ('message_inbox.purge_single_message');
            }
        }
        View::$vars->message = Language::GetText('success_messages_purged');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('messsage_inbox.purge_all_messages');

    }

// Delete message (Request came from view message page)
} else if (isset ($_GET['delete']) && is_numeric ($_GET['delete']) && $_GET['delete'] > 0) {

    $data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $_GET['delete']);
    $message_id = Message::Exist ($data);
    if ($message_id) {
        Message::Delete ($message_id);
        View::$vars->message = Language::GetText('success_messages_purged');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('message_inbox.delete_message');
    }

}





/******************
Prepare page to run
******************/

// Retrieve total count
$query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE recipient = " . View::$vars->user->user_id;
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('message_inbox.before_render');
View::Render ('myaccount/message_inbox.tpl');

?>