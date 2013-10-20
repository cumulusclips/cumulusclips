<?php

class VideoService extends ServiceAbstract
{
    /**
     * Retrieve the URL to a given video's play page
     * @param Video $video Instance of video to generate URL for
     * @return string Returns full URL to video's play page
     */
    public function getUrl(Video $video)
    {
        return HOST . '/videos/' . $video->videoId . '/' . Functions::createSlug($video->title);
    } 
    
    /**
     * Delete a video
     * @param Video $video Instance of video to be deleted
     * @return void Video is deleted from database and all related files and records are also deleted
     */
    public function delete(Video $video)
    {
        Plugin::triggerEvent('video.delete');

        // Delete files
        try {
            Filesystem::delete(UPLOAD_PATH . '/h264/' . $video->filename . '.mp4');
            Filesystem::delete(UPLOAD_PATH . '/theora/' . $video->filename . '.ogv');
            if (file_exists(UPLOAD_PATH . '/vp8/' . $video->filename . '.webm')) Filesystem::delete(UPLOAD_PATH . '/vp8/' . $video->filename . '.webm');
            Filesystem::delete(UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg');
            Filesystem::delete(UPLOAD_PATH . '/mobile/' . $video->filename . '.mp4');
        } catch (Exception $e) {
            App::Alert('Error During Video Removal', "Unable to delete video files for: $video->filename. The video has been removed from the system, but the files still remain. Error: " . $e->getMessage());
        }

        $commentService = new CommentService();
        $commentMapper = new CommentMapper();
        $comments = $commentMapper->getMultipleCommentsByCustom(array('video_id' => $video->videoId));
        foreach ($comments as $comment) $commentService->delete($comment);
        
        $ratingService = new RatingService();
        $ratingMapper = new RatingMapper();
        $ratings = $ratingMapper->getMultipleRatingsByCustom(array('video_id' => $video->videoId));
        foreach ($ratings as $rating) $ratingService->delete($rating);
        
        $favoriteService = new FavoriteService();
        $favoriteMapper = new FavoriteMapper();
        $favorites = $favoriteMapper->getMultipleFavoritesByCustom(array('video_id' => $video->videoId));
        foreach ($favorites as $favorite) $favoriteService->delete($favorite);
        
        $flagService = new FlagService();
        $flagMapper = new FlagMapper();
        $flags = $flagMapper->getMultipleFlagsByCustom(array('video_id' => $video->videoId));
        foreach ($flags as $flag) $flagService->delete($flag);

        $videoMapper = new VideoMapper();
        $videoMapper->delete($video->videoId);
    }

    /**
     * Generate a unique random string for a video filename
     * @return string Random video filename
     */
    public function createFilename()
    {
        $videoMapper = new VideoMapper();
        $filenameAvailable = null;
        do {
            $filename = Functions::random(20);
            if (!$videoMapper->getVideoByCustom(array('filename' => $$filename))) $filenameAvailable = true;
        } while (empty($filenameAvailable));
        return $filename;
    }

    /**
     * Generate a unique random url for accessing a private video
     * @return string URL for private video is returned
     */
    public function generatePrivate()
    {
        $videoMapper = new VideoMapper();
        $privateAvailable = null;
        do {
            $private = Functions::random(7);
            if (!$videoMapper->getVideoByCustom(array('private_url' => $private))) $privateAvailable = true;
        } while (empty($privateAvailable));
        return $private;
    }

    /**
     * Make a video visible to the public and notify subscribers of new video
     * @global object $config Site configuration settings
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void Video is activated, subscribers are notified, and admin
     * alerted. If approval is required video is marked as pending and placed in queue
     */
    public function Approve ($action)
    {
        App::LoadClass ('User');
        App::LoadClass ('Privacy');
        App::LoadClass ('Mail');
        
        $send_alert = false;
        Plugin::triggerEvent('video.before_approve');


        // 1) Admin created video in Admin Panel
        // 2) User created video
        // 3) Video is being approved by admin for first time
        if ((in_array ($action, array ('create','activate'))) || ($action == 'approve' && $this->released == 0)) {

            // User uploaded video but needs admin approval
            if ($action == 'activate' && Settings::Get ('auto_approve_videos') == '0') {

                // Send Admin Approval Alert
                $send_alert = true;
                $subject = 'New Video Awaiting Approval';
                $body = 'A new video has been uploaded and is awaiting admin approval.';

                // Set Pending
                $this->Update (array ('status' => 'pendingApproval'));
                Plugin::triggerEvent('video.approve_required');

            } else {

                // Send Admin Alert
                if (in_array ($action, array ('create','activate')) && Settings::Get ('alerts_videos') == '1') {
                    $send_alert = true;
                    $subject = 'New Video Uploaded';
                    $body = 'A new video has been uploaded.';
                }

                // Activate & Release
                $this->Update (array ('status' => 'approved', 'released' => 1));
                
                // Send video owner notification if opted-in
                $this->_notifyUserVideoIsReady();

                // Send subscribers notification if opted-in
                $query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = $this->user_id";
                $result = $this->db->Query ($query);
                while ($opt = $this->db->FetchObj ($result)) {

                    $subscriber = new User ($opt->user_id);
                    $privacy = Privacy::LoadByUser ($opt->user_id);
                    if ($privacy->OptCheck ('new_video')) {
                        $replacements = array (
                            'host'      => HOST,
                            'sitename'  => $this->_config->sitename,
                            'email'     => $subscriber->email,
                            'member'    => $this->username,
                            'title'     => $this->title,
                            'video_id'  => $this->video_id,
                            'slug'      => $this->slug
                        );
                        $mail = new Mail();
                        $mail->LoadTemplate ('new_video', $replacements);
                        $mail->Send ($subscriber->email);
                        Plugin::triggerEvent('video.notify_subscribers');
                    }

                }

                Plugin::triggerEvent('video.release');

            }

        // Video is being re-approved
        } else if ($action == 'approve' && $this->released != 0) {
            // Approve Video
            $this->Update (array ('status' => 'approved'));
            Plugin::triggerEvent('video.reapprove');
        }


        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Title: $this->title\n";
            $body .= "URL: $this->url\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }

        Plugin::triggerEvent('video.approve');

    }
    
    /**
     * Send video owner notification that video is now available 
     */
    protected function _notifyUserVideoIsReady()
    {
        $user = new User($this->user_id);
        $privacy = Privacy::LoadByUser($this->user_id);
        if ($privacy->OptCheck('videoReady')) {
            $replacements = array(
                'host'      => HOST,
                'sitename'  => $this->_config->sitename,
                'email'     => $user->email,
                'member'    => $this->username,
                'title'     => $this->title,
                'video_id'  => $this->video_id,
                'slug'      => $this->slug
            );
            $mail = new Mail();
            $mail->LoadTemplate('VideoReady', $replacements);
            $mail->Send($user->email);
            Plugin::trigger('video.notifyUserVideoIsReady', $this);
        }
    }
}