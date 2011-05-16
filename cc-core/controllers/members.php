<?php

### Created on March 5, 2009
### Created by Miguel A. Hurtado
### This script allows users to browse through channels


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Pagination');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('members');
Plugin::Trigger ('members.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 12;
$url = HOST . '/members';



// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE account_status = 'Active'";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output Page
View::SetLayout ('full.layout.tpl');
Plugin::Trigger ('members.before_render');
View::Render ('members.tpl');

?>