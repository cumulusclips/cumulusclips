<?php

// Init view
$view->InitView ('message_inbox');
Plugin::triggerEvent('message_inbox.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 20;
$url = HOST . '/myaccount/message/inbox';
$view->vars->message = null;
$messageMapper = new MessageMapper();

// Delete message (Request came from this page)
if (isset ($_POST['submitted'])) {

    // Verify messages were chosen
    if (!empty ($_POST['delete']) && is_array ($_POST['delete'])) {
        foreach($_POST['delete'] as $value){
            $message = $messageMapper->getMessageByCustom(array(
                'recipient' => $view->vars->loggedInUser->userId,
                'message_id' => $value)
            );
            if ($message) {
                $messageMapper->delete($message->messageId);
                Plugin::triggerEvent('message_inbox.purge_single_message');
            }
        }
        $view->vars->message = Language::getText('success_messages_purged');
        $view->vars->message_type = 'success';
        Plugin::triggerEvent('messsage_inbox.purge_all_messages');
    }

// Delete message (Request came from view message page)
} else if (!empty($_GET['delete']) && $_GET['delete'] > 0) {
    $message = $messageMapper->getMessageByCustom(array(
        'recipient' => $view->vars->loggedInUser->userId,
        'message_id' => $_GET['delete'])
    );
    if ($message) {
        $messageMapper->delete($message->messageId);
        $view->vars->message = Language::GetText('success_messages_purged');
        $view->vars->message_type = 'success';
        Plugin::triggerEvent('message_inbox.delete_message');
    }
}

// Retrieve total count
$query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE recipient = " . $view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$messageResults = $db->fetchAll($query);
$view->vars->messages = $messageMapper->getMessagesFromList(
    Functions::arrayColumn($messageResults, 'message_id')
);

// Output page
Plugin::triggerEvent('message_inbox.before_render');
$view->Render('myaccount/message_inbox.tpl');