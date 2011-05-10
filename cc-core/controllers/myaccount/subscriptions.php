<?php

### Created on April 5, 2009
### Created by Miguel A. Hurtado
### This script allows users to view their channel subscriptions


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Subscription');
App::LoadClass ('Pagination');
Plugin::Trigger ('subscriptions.start');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('subscriptions');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/subscriptions';
View::$vars->success = NULL;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['id']) && is_numeric ($_GET['id'])) {
    $data = array ('user_id' => View::$vars->user->user_id, 'member' => $_GET['id']);
    $id = Subscription::Exist ($data);
    if ($id) {
        Subscription::Delete ($id);
        View::$vars->success = Language::GetText('success_unsubscribed');
    }
}





/******************
Prepare page to run
******************/

// Retrieve total count
$query = "SELECT sub_id FROM subscriptions WHERE user_id = " . View::$vars->user->user_id;
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
Plugin::Trigger ('subscriptions.pre_render');
View::Render ('myaccount/subscriptions.tpl');

?>