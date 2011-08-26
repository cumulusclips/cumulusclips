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
        $this->date_created = date ('m/d/Y', strtotime ($row['date_created']));
        $this->comments_display = nl2br ($row['comments']);
        if ($this->user_id != 0) {
            $user = new User ($this->user_id);
            $this->name = $user->username;
            $this->email = $user->email;
            $this->website = HOST . '/members/' . $user->username . '/';
            $this->avatar_url = $user->avatar_url;
        } else {
            $this->avatar = THEME . '/images/user_placeholder.gif';
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
        $values = 'NOW(), ';

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
     * Make a comment visible to the public and notify video owner of comment
     * @param boolean $admin [optional] Whether an admin is performing approval or not
     * @return void If allowed, comment is approved and owner is notified
     * otherwise comment is marked as pending approval
     */
    public function Approve ($admin = false) {

        App::LoadClass ('User');
        App::LoadClass ('Video');
        App::LoadClass ('Privacy');
        App::LoadClass ('EmailTemplate');

        // Determine if video is allowed to be approved
        if ($admin || Settings::Get ('auto_approve_comments') == 'true') {

            $data = array ('status' => 'approved');

            // Execute approval actions if they haven't been executed before
            if ($this->released == 0) {

                $data['released'] = 1;

                ### Send video owner new comment notifition, if opted-in
                $privacy = Privacy::LoadByUser ($this->user_id);
                if ($privacy->OptCheck ('video_comment')) {
                    $video = new Video ($this->video_id);
                    $video_user = new User ($video->user_id);
                    $template = new EmailTemplate ('/video_comment.htm');
                    $template_data = array (
                        'host'   => HOST,
                        'email'  => $video_user->email,
                        'title'  => $video->title
                    );
                    $template->Replace ($template_data);
                    $template->Send ($video_user->email);
                }

            }

            $this->Update ($data);

        } else {
            $this->Update (array ('status' => 'pending'));
        }

    }

}

?>