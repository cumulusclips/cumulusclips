<?php

### Created on April 19, 2009
### Created by Miguel A. Hurtado
### This script allows users to compose messages


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Message');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');
Plugin::Trigger ('message_send.start');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('message_send');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->to = NULL;
View::$vars->subject = NULL;
View::$vars->msg = NULL;
View::$vars->Errors = array();
View::$vars->error_msg = NULL;
View::$vars->success = NULL;
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
    $id = Message::Exist ($data);
    if ($id) {

        $original_message = new Message ($id);
        View::$vars->to = $original_message->username;
        View::$vars->subject = "Re: $original_message->subject";
        View::$vars->msg = "\n\n\n> " . View::$vars->to . " wrote: \n\n $original_message->message";

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
                View::$vars->Errors['recipient'] = Language::GetText('error_recipient_self');
            }    

        } else {
            View::$vars->Errors['recipient'] = Language::GetText('error_recipient_exist');
        }

    } else {
        View::$vars->Errors['recipient'] = Language::GetText('error_recipient');
    }



    // Validate subject field
    if (!empty ($_POST['subject']) && !ctype_space ($_POST['subject'])) {
        $message['subject'] = htmlspecialchars ($_POST['subject']);
        View::$vars->subject = $message['subject'];
    } else {
        View::$vars->Errors['subject'] = Language::GetText('error_subject');
    }



    // Validate message field
    if (!empty ($_POST['message']) && !ctype_space ($_POST['message'])) {
        $message['message'] = htmlspecialchars ($_POST['message']);
        View::$vars->msg = $message['message'];
    } else {
        View::$vars->Errors['message'] = Language::GetText('error_message');
    }



    // Create message if no errors were found
    if (empty (View::$vars->Errors)) {

        $message['user_id'] = View::$vars->user->user_id;
        Message::Create ($message);
        View::$vars->success = Language::GetText('success_message_sent');
        View::$vars->to = NULL;
        View::$vars->subject = NULL;
        View::$vars->msg = NULL;

        // Send recipient email notification if opted-in
        $privacy = Privacy::LoadByUser ($recipient->user_id);
        if ($privacy->OptCheck ('new_message')) {
            $template = new EmailTemplate ('/new_message.htm');
            $Msg = array (
                'host'      => HOST,
                'sender'    => View::$vars->user->username,
                'email'     => $recipient->email
            );
            $template->Replace ($Msg);
            $template->Send ($recipient->email);
        }

    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }

}


// Output page
View::SetLayout ('portal.layout.tpl');
Plugin::Trigger ('message_send.pre_render');
View::Render ('myaccount/message_send.tpl');

?>