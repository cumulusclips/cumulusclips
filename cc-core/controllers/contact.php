<?php

Plugin::triggerEvent('contact.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$this->view->vars->errors = array();
$this->view->vars->name = null;
$this->view->vars->email = null;
$this->view->vars->feedback = null;
$this->view->vars->message = null;
$this->view->vars->messageType = null;
$config = Registry::get('config');

// Handle form if submitted
if (isset($_POST['submitted'])) {
	
    // Validate name
    if (!empty($_POST['name']) && !ctype_space($_POST['name'])) {
        $this->view->vars->name = trim($_POST['name']);
    } else {
        $this->view->vars->errors['name'] = Language::getText('error_name');
    }

    // Validate email
    $string = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i';
    if (!empty($_POST['email']) && !ctype_space($_POST['email']) && preg_match($string, $_POST['email'])) {
        $this->view->vars->email = trim($_POST['email']);
    } else {
        $this->view->vars->errors['email'] = Language::getText('error_email');
    }

    // Validate feedback
    if (!empty($_POST['feedback']) && !ctype_space($_POST['feedback'])) {
        $this->view->vars->feedback = trim($_POST['feedback']);
    } else {
        $this->view->vars->errors['feedback'] = Language::getText('error_message');
    }

    // Send email if no errors
    if (empty($this->view->vars->errors)) {
        $subject = 'Message received From ' . $config->sitename;
        $Msg = "Name: " . $this->view->vars->name . "\n";
        $Msg .= "E-mail: " . $this->view->vars->email . "\n";
        $Msg .= "Message:\n" . $this->view->vars->feedback;
        App::alert($subject, $Msg);

        $this->view->vars->messageType = 'success';
        $this->view->vars->message = Language::getText('success_contact_sent');
    } else {
        $this->view->vars->messageType = 'errors';
        $this->view->vars->message = Language::getText('errors_below');
        $this->view->vars->message .= '<br><br> - ' . implode('<br> - ', $this->view->vars->errors);
    }
}

Plugin::triggerEvent('contact.end');