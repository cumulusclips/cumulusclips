<?php

// Establish page variables, objects, arrays, etc
$this->view->options->disableView = true;
$userMapper = new UserMapper();

// Validate username length
if (empty($_POST['username']) || strlen($_POST['username']) < 4) {
    exit(json_encode (array ('result' => false, 'message' => (string) Language::getText('username_minimum'))));
}

// Validate username characters
if (!preg_match('/^[a-z0-9]+$/i', $_POST['username'])) {
    exit(json_encode (array ('result' => false, 'message' => (string) Language::getText('error_username'))));
}

// Check if username is in use
if ($userMapper->getUserByUsername($_POST['username'])) {
    echo json_encode (array ('result' => false, 'message' => (string) Language::getText('error_username_unavailable')));
} else {
    echo json_encode (array ('result' => true, 'message' => (string) Language::getText('username_available')));
}