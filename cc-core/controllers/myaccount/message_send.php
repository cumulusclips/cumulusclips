<?php

// Establish page variables, objects, arrays, etc
View::InitView ('message_send');
Plugin::Trigger ('message_send.start');
Functions::RedirectIf (View::$vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->to = NULL;
View::$vars->subject = NULL;
View::$vars->msg = NULL;
View::$vars->errors = array();
View::$vars->message = null;
$message = array();



// Verify if request came from outside page
if (isset ($_GET['username'])) {

    $username = trim ($_GET['username']);
    $data = array ('username' => $username);
    $id = User::Exist ($data);
    if ($id) {
        $recipient = new User ($id);
        View::$vars->to = $recipient->username;
    }

    
// Verify if request came from reply
} elseif (isset ($_GET['msg']) && is_numeric ($_GET['msg'])) {

    $message_id = trim ($_GET['msg']);
    $data = array ('message_id' => $message_id, 'recipient' => View::$vars->user->user_id);
    $message_id = Message::Exist ($data);
    if ($message_id) {
        $original_message = new Message ($message_id);
        View::$vars->to = $original_message->username;
        View::$vars->subject = "Re: $original_message->subject";
        View::$vars->msg = "\n\n\n> " . View::$vars->to . " wrote: \n\n $original_message->message";
        Plugin::Trigger ('message_send.load_original_message');
    }

}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate 'to' field
    if (!empty ($_POST['to']) && !ctype_space ($_POST['to'])) {

        $username = trim ($_POST['to']);
        $data = array ('username' => $username);
        $id = User::Exist ($data);
        if ($id) {
            
            $recipient = new User ($id);
            if ($recipient->user_id != View::$vars->user->user_id) {
                View::$vars->to = $recipient->username;
                $message['recipient'] = $recipient->user_id;
            } else {
                View::$vars->errors['recipient'] = Language::GetText('error_recipient_self');
            }    

        } else {
            View::$vars->errors['recipient'] = Language::GetText('error_recipient_exist');
        }

    } else {
        View::$vars->errors['recipient'] = Language::GetText('error_recipient');
    }



    // Validate subject field
    if (!empty ($_POST['subject']) && !ctype_space ($_POST['subject'])) {
        $message['subject'] = htmlspecialchars ($_POST['subject']);
        View::$vars->subject = $message['subject'];
    } else {
        View::$vars->errors['subject'] = Language::GetText('error_subject');
    }



    // Validate message field
    if (!empty ($_POST['message']) && !ctype_space ($_POST['message'])) {
        $message['message'] = htmlspecialchars ($_POST['message']);
        View::$vars->msg = $message['message'];
    } else {
        View::$vars->errors['message'] = Language::GetText('error_message');
    }



    // Create message if no errors were found
    if (empty (View::$vars->errors)) {

        $message['user_id'] = View::$vars->user->user_id;
        Plugin::Trigger ('message_send.before_send_message');
        Message::Create ($message);
        View::$vars->to = NULL;
        View::$vars->subject = NULL;
        View::$vars->msg = NULL;

        // Send recipient email notification if opted-in
        $privacy = Privacy::LoadByUser ($recipient->user_id);
        if ($privacy->OptCheck ('new_message')) {
            $replacements = array (
                'host'      => HOST,
                'sitename'  => $config->sitename,
                'sender'    => View::$vars->user->username,
                'email'     => $recipient->email
            );
            $mail = new Mail();
            $mail->LoadTemplate ('new_message', $replacements);
            $mail->Send ($recipient->email);
        }
        View::$vars->message = Language::GetText('success_message_sent');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('message_send.send_message');

    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }

}


// Output page
Plugin::Trigger ('message_send.before_render');
View::Render ('myaccount/message_send.tpl');