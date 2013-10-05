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
     * @param integer $video_id ID of video to be deleted
     * @return void Video is deleted from database and all related files and records are also deleted
     */
    static function Delete ($video_id) {

        App::LoadClass ('Rating');
        App::LoadClass ('Flag');
        App::LoadClass ('Favorite');
        App::LoadClass ('Comment');

        $db = Database::GetInstance();
        $video = new self ($video_id);
        Plugin::Trigger ('video.delete');

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



        // Delete Comments
        $query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE video_id = $video_id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Comment::Delete ($row->comment_id);

        // Delete Ratings
        $query = "SELECT rating_id FROM " . DB_PREFIX . "ratings WHERE video_id = $video_id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Rating::Delete ($row->rating_id);

        // Delete Favorites
        $query = "SELECT fav_id FROM " . DB_PREFIX . "favorites WHERE video_id = $video_id";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Favorite::Delete ($row->fav_id);

        // Delete Flags
        $query = "SELECT flag_id FROM " . DB_PREFIX . "flags WHERE id = $video_id AND type = 'video'";
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) Flag::Delete ($row->flag_id);

        // Delete Video
        $query = "DELETE FROM " . DB_PREFIX . "videos WHERE video_id = $video_id";
        $db->Query ($query);

    }

    /**
     * Generate a unique random string for a video filename
     * @return string Random video filename
     */
    static function CreateFilename() {
        $db = Database::GetInstance();
        do {
            $filename = Functions::Random(20);
            if (!self::Exist (array ('filename' => $filename))) $filename_available = true;
        } while (empty ($filename_available));
        return $filename;
    }

    /**
     * Generate a unique random url for accessing a private video
     * @return string URL for private video is returned
     */
    static function GeneratePrivate() {
        $db = Database::GetInstance();
        do {
            $private = Functions::Random(7);
            if (!self::Exist (array ('private_url' => $private))) $private_available = true;
        } while (empty ($private_available));
        return $private;
    }

    /**
     * Make a video visible to the public and notify subscribers of new video
     * @global object $config Site configuration settings
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void Video is activated, subscribers are notified, and admin
     * alerted. If approval is required video is marked as pending and placed in queue
     */
    public function Approve ($action) {

        App::LoadClass ('User');
        App::LoadClass ('Privacy');
        App::LoadClass ('Mail');
        
        $send_alert = false;
        Plugin::Trigger ('video.before_approve');


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
                Plugin::Trigger ('video.approve_required');

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
                        Plugin::Trigger ('video.notify_subscribers');
                    }

                }

                Plugin::Trigger ('video.release');

            }

        // Video is being re-approved
        } else if ($action == 'approve' && $this->released != 0) {
            // Approve Video
            $this->Update (array ('status' => 'approved'));
            Plugin::Trigger ('video.reapprove');
        }


        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Title: $this->title\n";
            $body .= "URL: $this->url\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }

        Plugin::Trigger ('video.approve');

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