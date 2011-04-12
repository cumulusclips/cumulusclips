<?php

### Created on May 10, 2009
### Created by Miguel A. Hurtado
### This script allows users to view and remove their favorite videos


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Pagination');
App::LoadClass ('Rating');
App::LoadClass ('Favorite');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->page_title = 'Techie Videos - My Favorite Videos';
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/myfavorites';
View::$vars->success = NULL;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    $data = array ('user_id' => View::$vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Favorite::Exist ($data);
    if ($id) {
        Favorite::Delete ($id);
        View::$vars->success = Language::GetText('success_favorite_removed');
    }

}


// Retrieve total count
$query = "SELECT video_id FROM favorites WHERE user_id = " . View::$vars->user->user_id;
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/myfavorites.tpl');

?>