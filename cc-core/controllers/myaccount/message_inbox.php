<?php

// Init view
View::InitView ('message_inbox');
Plugin::triggerEvent('message_inbox.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 20;
$url = HOST . '/myaccount/message/inbox';
View::$vars->message = null;

// Delete message (Request came from this page)
if (isset ($_POST['submitted'])) {

    // Verify messages were chosen
    if (!empty ($_POST['delete']) && is_array ($_POST['delete'])) {
        foreach($_POST['delete'] as $value){
            $data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $value);
            $message_id = Message::Exist ($data);
            if ($message_id) {
                Message::Delete ($message_id);
                Plugin::triggerEvent('message_inbox.purge_single_message');
            }
        }
        View::$vars->message = Language::GetText('success_messages_purged');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('messsage_inbox.purge_all_messages');
    }

// Delete message (Request came from view message page)
} else if (isset ($_GET['delete']) && is_numeric ($_GET['delete']) && $_GET['delete'] > 0) {
    
    $data = array ('recipient' => View::$vars->user->user_id, 'message_id' => $_GET['delete']);
    $message_id = Message::Exist ($data);
    if ($message_id) {
        Message::Delete ($message_id);
        View::$vars->message = Language::GetText('success_messages_purged');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('message_inbox.delete_message');
    }
}

// Retrieve total count
$query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE recipient = " . View::$vars->user->user_id;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$messageResults = $db->fetchAll($query);
$messageMapper = new MessageMapper();
View::$vars->messages = $messageMapper->getMultipleMessagesById(
    Functions::flattenArray($messageResults, 'message_id')
);

// Output page
Plugin::triggerEvent('message_inbox.before_render');
View::Render('myaccount/message_inbox.tpl');