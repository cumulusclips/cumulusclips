<?php

class Video {

    public $found;
    private $db;
    protected static $table = 'videos';
    protected static $id_name = 'video_id';


    /**
     * Instantiate object
     * @param integer $id ID of record to be instantiated
     * @return object Returns object of class type
     */
    public function  __construct ($id) {
        $this->db = Database::GetInstance();
        if (self::Exist (array (self::$id_name => $id))) {
            $this->Get ($id);
            $this->found = true;
        } else {
            $this->found = false;
        }
    }




    /**
     * Extract values from database and set them to object properties
     * @param integer $id ID of record to be instantiated
     * @return void DB record's fields are loaded into object properties
     */
    private function Get ($id) {
        $query = "SELECT " . DB_PREFIX . self::$table . ".*, username FROM " . DB_PREFIX . self::$table . " INNER JOIN " . DB_PREFIX . "users on " . DB_PREFIX . self::$table . ".user_id = " . DB_PREFIX . "users.user_id WHERE " . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        // Video Specific values
        $this->tags = preg_split ('/\s?,\s?/', $this->tags);
        $this->duration = (substr ($this->duration,0,3) == '00:')?substr ($this->duration,3):$this->duration;
        $this->slug = Functions::CreateSlug($this->title);
        $this->date_created_formatted = date ('m/d/Y', strtotime ($this->date_created));
        $this->url = HOST . '/videos/' . $this->video_id . '/' . $this->slug;
        Plugin::Trigger ('video.get');

    }




    /**
     * Check if a record exists matching the given criteria
     * @param array $data Key/Value pairs to use in select criteria i.e. array (field_name => value)
     * @return integer|boolean Returns record ID if record is found or boolean false if not found
     */
    static function Exist ($data) {

        $db = Database::GetInstance();
        $query = 'SELECT ' . self::$id_name . ' FROM ' . DB_PREFIX . self::$table . ' WHERE';

        foreach ($data as $key => $value) {
            $value = $db->Escape ($value);
            $query .= " $key = '$value' AND";
        }

        $query = substr ($query, 0, -4);
        $result = $db->Query ($query);

        if ($db->Count($result) > 0) {
            $row = $db->FetchAssoc ($result);
            return $row[self::$id_name];
        } else {
            return false;
        }

    }




    /**
     * Create a new record using the given criteria
     * @param array $data Key/Value pairs to use as data for new record i.e. array (field_name => value)
     * @return integer Returns the ID of the newly created record
     */
    static function Create ($data) {

        $db = Database::GetInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . self::$table;
        $fields = 'date_created, ';
        $values = 'NOW(), ';

        Plugin::Trigger ('video.before_save');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        Plugin::Trigger ('video.create');
        return $db->LastId();

    }




    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void Record is updated in DB
     */
    public function Update ($data) {

        Plugin::Trigger ('video.before_update');
        $query = 'UPDATE ' . DB_PREFIX . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('video.update');

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
        @unlink(UPLOAD_PATH . '/' . $video->filename . '.flv');
        @unlink(UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg');
        @unlink(UPLOAD_PATH . '/mp4/' . $video->filename . '.mp4');


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
        
        global $config;
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
                $this->Update (array ('status' => 'pending approval'));
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

                // Send subscribers notification if opted-in
                $query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = $this->user_id";
                $result = $this->db->Query ($query);
                while ($opt = $this->db->FetchObj ($result)) {

                    $subscriber = new User ($opt->user_id);
                    $privacy = Privacy::LoadByUser ($opt->user_id);
                    if ($privacy->OptCheck ('new_video')) {
                        $replacements = array (
                            'host'      => HOST,
                            'sitename'  => $config->sitename,
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

}

?>