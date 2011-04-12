<?php

### Created on May 8, 2010
### Created by Miguel A. Hurtado
### This script receives notification that a video has finished processecing at Encoding.com and updates its status


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('Encoding');
App::LoadClass ('Privacy');
App::LoadClass ('User');
App::LoadClass ('EmailTemplate');
App::LoadClass ('cloudfiles', VENDORS . '/rackspace_cloud_api');



// Establish page variables, objects, arrays, etc
$xml = NULL;
$result = NULL;
$container = array();



// Parse result XML
if (isset ($_POST['xml'])) {

    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (LOG . '/results.xml', urldecode ($_POST['xml']));
        Logger (CONVERSION_LOG, 'Parsing Encoding.com result XML...');
    }

    ### Report results
    try {
        $xml = new SimpleXMLElement (urldecode ($_POST['xml']));
    } catch (Exception $e) {

        // Notify script was called and provided with invalid XML
        $msg = "An error occured in the Encoding.com notify script.\n\nCaught exception:\n" . $e->getMessage();
        Logger (CONVERSION_LOG, $msg);
        mail (MAIN_EMAIL, 'Error during video encoding', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
        exit();

    }




    // Debug Log
    (DEBUG_CONVERSION) ? Logger (CONVERSION_LOG, 'Validating required result XML fields...') : NULL;

    ### Verify XML is valid
    if (empty ($xml->status) || empty ($xml->format) || empty ($xml->mediaid)) {

        // Notify script was called and provided with invalid XML
        $msg = "An error occured in the Encoding.com notify script.\n\nInvalid XML file provided:\n" . print_r ($xml, TRUE);
        Logger (CONVERSION_LOG, $msg);
        mail (MAIN_EMAIL, 'Error during video encoding', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
        exit();

    }




    // Debug Log
    (DEBUG_CONVERSION) ? Logger (CONVERSION_LOG, 'Matching job id with corresponding video...') : NULL;

    ### Retrieve associated video
    $job_id = $xml->mediaid;
    $video_id = Video::Exist (array ('job_id' => $job_id, 'status' => 5), $db);
    if (!$video_id) {

        // No video associated with job_id
        $msg = "An error occured in the Encoding.com notify script.\n\nNo video associated with that job_id:\n" . $job_id;
        Logger (CONVERSION_LOG, $msg);
        mail (MAIN_EMAIL, 'Error during video encoding', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
        exit();

    }
    $video = new Video ($video_id, $db);




    // Debug Log
    (DEBUG_CONVERSION) ? Logger (CONVERSION_LOG, 'Checking if transcoding was successful...') : NULL;

    ### Verify transcoding was successful
    $result = Encoding::GetEncodingResults ($xml);
    if (!$result) {

        // Transcoding Failed
        $msg = 'There were errors transcoding video number ' . $video->video_id . ". Below are the results from Encoding.com:\n" . print_r ($xml, true);
        Logger (CONVERSION_LOG, $msg);
        mail (MAIN_EMAIL, 'Error during video encoding', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
        $video->Update (array ('status' => 9));
        exit();

    }




    // Debug Log
    (DEBUG_CONVERSION) ? Logger (CONVERSION_LOG, 'Verifying files were created at Rackspace...') : NULL;

    // CloudFiles PHP API... Authenticate... Connect...
    $auth = new CF_Authentication ($config->rs_user, $config->rs_key);
    $auth->authenticate();
    $conn = new CF_Connection ($auth);

    // Retrieve containers
    $container[] = $conn->get_container ($config->flv_bucket);
    $container[] = $conn->get_container ($config->mp4_bucket);
    $container[] = $conn->get_container ($config->thumb_bucket);

    // Verify all files were created at Rackspace
    foreach ($container as $cont) {

        (DEBUG_CONVERSION) ? Logger (CONVERSION_LOG, $cont) : NULL;

        $objects = array();
        $objects = $cont->list_objects (0, NULL, $video->filename);
        if (count ($objects) != 1) {
            // Log Error
            $msg = "An error occured in the Encoding.com notify script.\n\nSome output files were not found on Rackspace. The video_id is: " . $video->video_id;
            Logger (CONVERSION_LOG, $msg);
            mail (MAIN_EMAIL, 'Error during video encoding', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
            $video->Update (array ('status' => 9));
            exit();
        }
    }




    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, 'Initializing video post processing...');
    }

    ### Post Processing

    // Retrieve Video Duration
    // Update video info in DB
    // Notify subscribed users of new video
    // Clean up




    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, 'Retrieving video duration...');
    }

    ### Retrieve video's duration
    $duration_result = Encoding::GetVideoDuration ($job_id);
    if ($duration_result) {
        $duration = FormatSeconds ($duration_result);
    }




    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, 'Updating video status...');
    }

    ### Update database with new video status information
    $video_status = 6;
    $video->Update (array ('status' => $video_status, 'duration' => $duration));
    @mail (MAIN_EMAIL, 'New Video', "ID: " . $video->video_id . "\nTitle: " . $video->title);



    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, 'Notifying users of new video...');
    }

    ### Send subscribers notification if opted-in
    $query = "SELECT user_id FROM subscriptions WHERE channel = $video->user_id";
    $result_alert = $db->Query ($query);
    while ($opt = $db->FetchRow ($result_alert)) {

        $subscriber = new User ($opt[0], $db);
        $privacy = new Privacy ($opt[0], $db);
        if ($privacy->OptCheck ('new_video')) {
            if (preg_match ('/@techievideos\.com$', $subscriber->email)) continue;  // Don't send email for internal accounts
            $template = new EmailTemplate ('/new_video.htm');
            $template_data = array (
                'host'      => HOST,
                'email'     => $subscriber->email,
                'channel'   => $user->username,
                'title'     => $video->title,
                'video_id'  => $video->video_id,
                'dashed'    => $video->dashed
            );
            $template->Replace ($template_data);
            $template->Send ($subscriber->email);
        }

    }




    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, 'Deleting raw video...');
    }

    ### Delete Original Video
    if (!unlink (UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->original_extension)) {
        $msg = "An error occured in the Encoding.com notify script.\n\nUnable to delete the raw video. The id of the video is: $video->video_id";
        mail (MAIN_EMAIL, 'Error During Post-Processing', $msg, 'From: Admin - TechieVideos.com<admin@techievideos.com>');
        Logger (CONVERSION_LOG, $msg);
    }




    // Debug Log
    if (DEBUG_CONVERSION) {
        Logger (CONVERSION_LOG, "Video ID: $video->video_id, has completed processing!\n");
    }

} else {
    Logger (CONVERSION_LOG, 'XML not passed via POST');
}

?>