<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Pagination');
App::LoadClass ('Rating');
App::LoadClass ('Favorite');


// Establish page variables, objects, arrays, etc
View::InitView ('myfavorites');
Plugin::Trigger ('myfavorites.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/myfavorites';
View::$vars->message = null;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    $data = array ('user_id' => View::$vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Favorite::Exist ($data);
    if ($id) {
        Favorite::Delete ($id);
        View::$vars->message = Language::GetText('success_favorite_removed');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('myfavorites.remove_favorite');
    }

}


// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "favorites WHERE user_id = " . View::$vars->user->user_id;
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('myfavorites.before_render');
View::Render ('myaccount/myfavorites.tpl');

?>