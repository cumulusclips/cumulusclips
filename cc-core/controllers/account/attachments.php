<?php

Plugin::triggerEvent('attachments.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$db = Registry::get('db');
$fileMapper = new \FileMapper();
$fileService = new \FileService();
$recordsPerPage = 20;
$url = HOST . '/account/attachments';
$params = array(':type' => \FileMapper::TYPE_ATTACHMENT);
$this->view->vars->message = null;
$this->view->vars->messageType = null;

// Handle Delete attachments form if submitted
if (!empty($_POST['delete']) && is_array($_POST['delete'])) {

    foreach($_POST['delete'] as $value){

        $file = $fileMapper->getByCustom(array(
            'user_id' => $this->view->vars->loggedInUser->userId,
            'type' => \FileMapper::TYPE_ATTACHMENT,
            'file_id' => $value
        ));

        // Deleter file
        if ($file) {
            $fileService->delete($file);
        }
    }

    $this->view->vars->message = Language::getText('success_attachments_deleted');
    $this->view->vars->messageType = 'success';
}

// Retrieve total count
$query = "SELECT file_id FROM " . DB_PREFIX . "files "
    . " WHERE "
    . " user_id = " . $this->view->vars->loggedInUser->userId
    . " AND type = :type";
$db->fetchAll($query, $params);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $recordsPerPage);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $recordsPerPage";
$fileResults = $db->fetchAll($query, $params);
$this->view->vars->userAttachments = $fileMapper->getFromList(
    Functions::arrayColumn($fileResults, 'file_id')
);

Plugin::triggerEvent('attachments.end');
