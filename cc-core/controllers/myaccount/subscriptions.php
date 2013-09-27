<?php

// Establish page variables, objects, arrays, etc
View::InitView ('subscriptions');
Plugin::Trigger ('subscriptions.start');
Functions::RedirectIf (View::$vars->logged_in = UserService::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/subscriptions';
View::$vars->message = null;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['id']) && is_numeric ($_GET['id'])) {
    $data = array ('user_id' => View::$vars->user->user_id, 'member' => $_GET['id']);
    $id = Subscription::Exist ($data);
    if ($id) {
        $subscribed_user = new User ($_GET['id']);
        Subscription::Delete ($id);
        View::$vars->message = Language::GetText('success_unsubscribed', array ('username' => $subscribed_user->username));
        View::$vars->message_type = 'success';
        Plugin::Trigger ('subscriptions.unsubscribe');
    }
}





/******************
Prepare page to run
******************/

// Retrieve total count
$query = "SELECT sub_id FROM " . DB_PREFIX . "subscriptions WHERE user_id = " . View::$vars->user->user_id;
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);


// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('subscriptions.before_render');
View::Render ('myaccount/subscriptions.tpl');