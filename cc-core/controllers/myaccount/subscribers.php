<?php

### Created on April 7, 2009
### Created by Miguel A. Hurtado
### This script displays the users who are subscribed to the current users channel


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Subscription');
App::LoadClass ('Pagination');
Plugin::Trigger ('subscribers.start');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('subscribers');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/subscribers';



// Retrieve total count
$query = "SELECT user_id FROM subscriptions WHERE member = " . View::$vars->user->user_id;
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
Plugin::Trigger ('subscribers.pre_render');
View::Render ('myaccount/subscribers.tpl');

?>