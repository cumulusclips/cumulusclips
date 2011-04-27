<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Subscription');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);




/***********************
Handle page if submitted
***********************/

if (isset ($_POST['action'])) {
	
    switch ($_POST['action']) {

        ### Handle subscribe user to a member
        case 'subscribe':
            
            // Verify user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to subscribe to members!'));
                exit();
            }

            // Check if user is subscribing to himself
            if ($user->user_id == $video->user_id) {
                echo json_encode (array ('result' => 0, 'msg' => 'You can\'t subscribe to yourself!'));
                exit();
            }

            // Create subscription record if none exists
            $data = array ('member' => $video->user_id, 'user_id' => $user->user_id);
            if (!Subscription::Exist ($data)) {
                $subscribed = Subscription::Create ($data);
                echo json_encode (array ('result' => 1, 'msg' => 'You have subscribed to ' . $video->username . '!'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You\'re already subscribed to this member!'));
                exit();
            }




        ### Handle unsubscribe user from a member
        case 'unsubscribe':
		
            // Verify user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to subscribe to members!'));
                exit();
            }

            // Delete subscription if one exists
            if ($subscribed) {
                Subscription::Delete ($subscribed);
                echo json_encode (array ('result' => 1, 'msg' => 'You have unsubscribed from ' . $video->username . '!'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You\'re not subscribed to this member!'));
                exit();
            }		




        ### Invalid action
        default:
            App::Throw404();

			
        }   // END action switch
	
	
}   // END verify if page was submitted

?>