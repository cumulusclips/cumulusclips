<?php

// Establish page variables, objects, arrays, etc
$this->view->InitView ('myfavorites');
Plugin::Trigger ('myfavorites.start');
Functions::RedirectIf ($this->view->vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
$this->view->vars->user = new User ($this->view->vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/myfavorites';
$this->view->vars->message = null;
$db = Registry::get('db');





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['vid']) && is_numeric ($_GET['vid']) && $_GET['vid'] != 0) {

    $data = array ('user_id' => $this->view->vars->user->user_id, 'video_id' => $_GET['vid']);
    $id = Favorite::Exist ($data);
    if ($id) {
        Favorite::Delete ($id);
        $this->view->vars->message = Language::GetText('success_favorite_removed');
        $this->view->vars->message_type = 'success';
        Plugin::Trigger ('myfavorites.remove_favorite');
    }

}


// Retrieve total count
$query = "SELECT " . DB_PREFIX . "favorites.video_id FROM " . DB_PREFIX . "favorites INNER JOIN " . DB_PREFIX . "videos ON " . DB_PREFIX . "favorites.video_id = " . DB_PREFIX . "videos.video_id WHERE status = 'approved' AND private = '0' AND " . DB_PREFIX . "favorites.user_id = " . $this->view->vars->user->user_id . " ORDER BY " . DB_PREFIX . "favorites.date_created DESC";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$this->view->vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$this->view->vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('myfavorites.before_render');
$this->view->Render ('myaccount/myfavorites.tpl');