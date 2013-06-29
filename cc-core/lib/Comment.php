<?php

class Comment {

    public $found;
    private $db;
    protected static $table = 'comments';
    protected static $id_name = 'comment_id';


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
     * @return void
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . DB_PREFIX . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        // Custom Vars
        $this->date_created = Functions::GmtToLocal ($this->date_created);
        $this->comments_display = nl2br ($row['comments']);
        if ($this->user_id != 0) {
            $user = new User ($this->user_id);
            $this->name = $user->username;
            $this->email = $user->email;
            $this->website = HOST . '/members/' . $user->username . '/';
            $this->avatar_url = $user->avatar_url;
        } else {
            $this->avatar_url = View::GetFallbackUrl ('images/avatar.gif');
        }
        Plugin::Trigger ('comment.get');

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
        $values = "'" . gmdate (DATE_FORMAT) . "', ";

        Plugin::Trigger ('comment.before_create');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        Plugin::Trigger ('comment.create');
        return $db->LastId();

    }




    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void
     */
    public function Update ($data) {

        Plugin::Trigger ('comment.before_update');
        $query = 'UPDATE ' . DB_PREFIX . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('comment.update');

    }




    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('comment.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }




    /**
     * Make a comment visible to the public and notify user of new comment
     * @global object $config Site configuration settings
     * @param string $action Step in the approval proccess to perform. Allowed values: create|activate|approve
     * @return void Comment is activated, user is notified, and admin alerted.
     * If approval is required comment is marked pending and placed in queue
     */
    public function Approve ($action) {

        App::LoadClass ('User');
        App::LoadClass ('Video');
        App::LoadClass ('Privacy');
        App::LoadClass ('Mail');

        global $config;
        $send_alert = false;
        $video = new Video ($this->video_id);
        Plugin::Trigger ('comment.before_approve');

        
        // 1) Admin posted comment in Admin Panel
        // 2) Comment is posted by user
        // 3) Comment is being approved by admin for first time
        if ((in_array ($action, array ('create','activate'))) || $action == 'approve' && $this->released == 0) {

            // Comment is being posted by user, but approval is required
            if ($action == 'activate' && Settings::Get ('auto_approve_comments') == '0') {

                // Send Admin Approval Alert
                $send_alert = true;
                $subject = 'New Comment Awaiting Approval';
                $body = 'A new comment has been posted and is awaiting admin approval.';

                // Set Pending
                $this->Update (array ('status' => 'pending'));
                Plugin::Trigger ('comment.approve_required');

            } else {

                // Send Admin Alert
                if (in_array ($action, array ('create','activate')) && Settings::Get ('alerts_comments') == '1') {
                    $send_alert = true;
                    $subject = 'New Comment Posted';
                    $body = 'A new comment has been posted.';
                }

                // Activate & Release
                $this->Update (array ('status' => 'approved', 'released' => 1));

                // Send video owner new comment notifition, if opted-in
                $privacy = Privacy::LoadByUser ($video->user_id);
                if ($privacy->OptCheck ('video_comment')) {
                    $user = new User ($video->user_id);
                    $replacements = array (
                        'host'      => HOST,
                        'sitename'  => $config->sitename,
                        'email'     => $user->email,
                        'title'     => $video->title
                    );
                    $mail = new Mail();
                    $mail->LoadTemplate ('video_comment', $replacements);
                    $mail->Send ($user->email);
                    Plugin::Trigger ('comment.notify_member');
                }

                Plugin::Trigger ('comment.release');

            }

        // Comment is being re-approved
        } else if ($action == 'approve' && $this->released != 0) {
            // Activate Comment
            $this->Update (array ('status' => 'approved'));
            Plugin::Trigger ('comment.reapprove');
        }


        // Send admin alert
        if ($send_alert) {
            $body .= "\n\n=======================================================\n";
            $body .= "Author: $this->name\n";
            $body .= "Video URL: $video->url/\n";
            $body .= "Comments: $this->comments\n";
            $body .= "=======================================================";
            App::Alert ($subject, $body);
        }

        Plugin::Trigger ('comment.approve');

    }

}

?>