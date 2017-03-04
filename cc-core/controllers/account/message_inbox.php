<?php

Plugin::triggerEvent('message_inbox.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$records_per_page = 20;
$url = HOST . '/account/message/inbox';
$this->view->vars->message = null;
$messageMapper = new MessageMapper();
$messageService = new MessageService();
$db = Registry::get('db');

// Delete message (Request came from this page)
if (isset ($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        // Verify messages were chosen
        if (!empty ($_POST['delete']) && is_array ($_POST['delete'])) {
            foreach($_POST['delete'] as $value){
                $message = $messageMapper->getMessageByCustom(array(
                    'recipient' => $this->view->vars->loggedInUser->userId,
                    'message_id' => $value)
                );
                if ($message) {
                    $messageService->delete($message);
                }
            }
            $this->view->vars->message = Language::getText('success_messages_purged');
            $this->view->vars->message_type = 'success';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }

// Delete message (Request came from view message page)
} else if (!empty($_GET['delete']) && $_GET['delete'] > 0) {
    $message = $messageMapper->getMessageByCustom(array(
        'recipient' => $this->view->vars->loggedInUser->userId,
        'message_id' => $_GET['delete'])
    );
    if ($message) {
        $messageMapper->delete($message->messageId);
        $this->view->vars->message = Language::GetText('success_messages_purged');
        $this->view->vars->message_type = 'success';
    }
}

// Retrieve total count
$query = "SELECT message_id FROM " . DB_PREFIX . "messages WHERE recipient = " . $this->view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$messageResults = $db->fetchAll($query);
$this->view->vars->messages = $messageMapper->getMessagesFromList(
    Functions::arrayColumn($messageResults, 'message_id')
);

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('message_inbox.end');
