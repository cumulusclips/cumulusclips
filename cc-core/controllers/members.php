<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
View::InitView ('members');
Plugin::Trigger ('members.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 12;
$url = HOST . '/members';



// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE status = 'Active'";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output Page
Plugin::Trigger ('members.before_render');
View::Render ('members.tpl');

?>