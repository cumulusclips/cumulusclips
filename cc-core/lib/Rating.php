<?php

class Rating {

    public $found;
    private $db;
    protected static $table = 'ratings';
    protected static $id_name = 'rating_id';



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
     ** @return void DB record's fields are loaded into object properties
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        Plugin::Trigger ('rating.get');
    }



    /**
     * Check if a record exists matching the given criteria
     * @param array $data Key/Value pairs to use in select criteria i.e. array (field_name => value)
     * @return integer|boolean Returns record ID if record is found or boolean false if not found
     */
    static function Exist ($data) {

        $db = Database::GetInstance();
        $query = 'SELECT ' . self::$id_name . ' FROM ' . self::$table . ' WHERE';

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
        $query = 'INSERT INTO ' . self::$table;
        $fields = 'date_created, ';
        $values = 'NOW(), ';

        Plugin::Trigger ('rating.before_create');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        Plugin::Trigger ('rating.create');
        return $db->LastId();

    }



    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void Record is updated in DB
     */
    public function Update ($data) {

        Plugin::Trigger ('rating.before_update');
        $query = 'UPDATE ' . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('rating.update');

    }



    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('rating.delete');
        $query = "DELETE FROM " . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }



    /**
     * Rate a video based on like / dislike
     * @param integer $rating Rating being given (1 = Like / 0 = Dislike)
     * @param integer $video_id Video being rated
     * @param integer $user_id User doing the rating
     * @return boolean Returns boolean true if rating was added, false if user
     * has already rated video
     */
    static function AddRating ($rating, $video_id, $user_id) {
        if (!self::Exist (array ('video_id' => $video_id, 'user_id' => $user_id))) {
            $rating = ($rating == '1') ? 1 : 0;
            $data = array ('video_id' => $video_id, 'user_id' => $user_id, 'rating' => $rating);
            self::Create ($data);
            return true;
        } else {
            return false;
        }
    }



    /**
     * Retrieve total number of like ratings for a video
     * @param integer $video_id Video to retrieve likes for
     * @return integer Returns total likes
     */
    static function GetLikeCount ($video_id) {
        $db = Database::GetInstance();
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $video_id AND rating = 1";
        $result = $db->Query ($query);
        $count = $db->FetchRow ($result);
        return $count[0];
    }



    /**
     * Retrieve total number of dislike ratings for a video
     * @param integer $video_id Video to retrieve dislikes for
     * @return integer Returns total dislikes
     */
    static function GetDislikeCount ($video_id) {
        $db = Database::GetInstance();
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $video_id AND rating = 0";
        $result = $db->Query ($query);
        $count = $db->FetchRow ($result);
        return $count[0];
    }



    /**
     * Retrieve total number of ratings for a video
     * @param integer $video_id Video to retrieve rating count for
     * @return integer Returns total number of ratings
     */
    static function GetCount ($video_id) {
        $db = Database::GetInstance();
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $video_id";
        $result = $db->Query ($query);
        $count = $db->FetchRow ($result);
        return $count[0];
    }



    /**
     * Retrieve ratings for a video
     * @param integer $video_id Video to retrieve ratings for
     * @return object Returns stdClass object with properties for (dis)like
     * counts & text, and total ratings
     */
    static function GetRating ($video_id) {

        $db = Database::GetInstance();
        $rating = new stdClass();

        // Total
        $rating->count = self::GetCount ($video_id);

        // Like
        $like_count = self::GetLikeCount ($video_id);
        $rating->likes = $like_count;
        $rating->like_text = Language::GetText('like');

        // Dislike
        $dislike_count = self::GetDislikeCount ($video_id);
        $rating->dislikes = $dislike_count;
        $rating->dislike_text = Language::GetText('dislike');

        return $rating;

    }



    /**
     * (LEGACY) Get average rating for a video (rated on 5 point scale)
     * @param integer $video_id Video to retrieve rating for
     * @return integer Returns average rating (5 Highest, 0 Lowest or none)
     */
    static function GetFiveScaleRating ($video_id) {
        $count = self::GetCount ($video_id);
        if ($count == 0) return 0;
        $vote_total = self::GetLikeCount ($video_id)*5;
        $average = $vote_total/$count;
        return floor($average);
    }

}

?>