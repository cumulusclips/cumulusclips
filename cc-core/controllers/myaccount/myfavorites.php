<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('myfavorites');
Plugin::Trigger ('myfavorites.start');
Functions::RedirectIf ($view->vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
$view->vars->user = new User ($view->vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/myfavorites';
$view->vars->message = null;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    $data = array ('user_id' => $view->vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Favorite::Exist ($data);
    if ($id) {
        Favorite::Delete ($id);
        $view->vars->message = Language::GetText('success_favorite_removed');
        $view->vars->message_type = 'success';
        Plugin::Trigger ('myfavorites.remove_favorite');
    }

}


// Retrieve total count
$query = "SELECT " . DB_PREFIX . "favorites.video_id FROM " . DB_PREFIX . "favorites INNER JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "favorites.video_id = " . DB_PREFIX . "videos.video_id WHERE status = 'approved' AND private = '0' AND " . DB_PREFIX . "favorites.user_id = " . $view->vars->user->user_id . " ORDER BY " . DB_PREFIX . "favorites.date_created DESC";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$view->vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = $view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$view->vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('myfavorites.before_render');
$view->Render ('myaccount/myfavorites.tpl');