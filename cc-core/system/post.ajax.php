<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Post');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if (!$logged_in) App::Throw404();
$user = new User ($logged_in);
$data = array();





/***********************
Handle page if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Save update if no errors were found
    if (!empty ($_POST['post']) && !ctype_space ($_POST['post'])) {

        $data['post'] = htmlspecialchars (trim ($_POST['post']));
        $data['user_id'] = $user->user_id;
        $post_id = Post::Create ($data);
        $post = new Post ($post_id);

        // Retrieve new formatted status updated
        View::InitView();
        ob_start();
        View::RepeatingBlock('post.tpl', array ($post->post_id));
        $status_update = ob_get_contents();
        ob_end_clean();

        echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_status_updated'), 'other' => $status_update));
        exit();

    } else {
        echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText ('error_status_update')));
        exit();
    }

}   // END verify if page was submitted

?>