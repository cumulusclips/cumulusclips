<?php

### Created on December 31, 2010
### Created by Miguel A. Hurtado
### This script creates an encoding job for a video FTP'd directly to server

// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
include (DOC_ROOT . '/includes/functions.php');
App::LoadClass ('DBConnection.php');
App::LoadClass ('KillApp.php');
App::LoadClass ('Login.php');
App::LoadClass ('Video.php');
App::LoadClass ('Encoding.php');
App::LoadClass ('User.php');


// Establish page variables, objects, arrays, etc
session_start();
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);
$login = new Login ($db);
$logged_in = $login->LoginCheck();
if ($logged_in != 22) Throw404(); // Only allow TechieVideos user
$user = new User ($logged_in, $db);
$page_title = 'Start Encoding - Admin Techie Videos';
$content_file = 'admin/start_encoding.tpl';
$Errors = NULL;
$Success = NULL;






### Handle form if submitted
if (isset ($_POST['submitted'])) {

    // Validate filename
    if (!empty ($_POST['filename']) && preg_match ('/^[a-z0-9]{20}+\.[a-z0-9]{2,5}$/i', $_POST['filename'])) {

        // Verify file exists
        if (file_exists (UPLOAD_PATH . '/temp/' . $_POST['filename'])) {
            $extension = GetExtension ($_POST['filename']);
            $filename = str_replace (".$extension", '', $_POST['filename']);

            // Find corresponding video
            $id = Video::Exist (array ('filename' => $filename), $db);
            if ($id) {
                $job = Encoding::CreateEncodingJob ($filename, $extension);
                $video = new Video ($id, $db);
                $video->Update (array ('status' => 5, 'job_id' => $job));
                $Success = '<div id="success">Encoding has begun. Your job number is: ' . $job . '</div>';
            } else {
                echo $id;
                $Errors = '<div id="errors-found">No matching video<br />Please try again.</div>';
            }

        } else {
            $Errors = '<div id="errors-found">No such file<br />Please try again.</div>';
        }

    } else {
        $Errors = '<div id="errors-found">Invalid filename<br />Please try again.</div>';
    }

}

include (THEMES . '/layouts/admin.layout.tpl');

?>