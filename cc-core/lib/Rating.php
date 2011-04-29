<?php

class Rating {

    public $db;
    public $video_id;



    // Construct Method
    public function __construct ($video_id) {
        $this->video_id = $video_id;
        $this->db = Database::GetInstance();
    }



    // Rate a video method
    public function AddVote ($user_id, $rating) {
        $query = "SELECT rating_id FROM ratings WHERE video_id = $this->video_id AND user_id = $user_id";
        $result = $this->db->Query ($query);
        if ($this->db->Count ($result) == 0) {
            $rating_num = ($rating == '1') ? 1 : 0;
            $query = "INSERT INTO ratings (video_id, user_id, date_rated, rating) VALUES ($this->video_id, $user_id, NOW(), $rating_num)";
            $this->db->Query ($query);
            $msg = "User: $user_id\nVideo: $this->video_id\nRating: $rating";
            @mail (MAIN_EMAIL, 'Video Has Been Rated', $msg, 'From: Admin - TechieVideos.com <admin@techievideos.com>');
            return TRUE;
        } else {
            return FALSE;
        }
    }



    // Total number of like votes method
    public function GetLikeCount() {
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $this->video_id AND rating = 1";
        $result = $this->db->Query ($query);
        $count = $this->db->FetchRow ($result);
        return $count[0];
    }



    // Total number of dislike votes method
    public function GetDislikeCount() {
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $this->video_id AND rating = 0";
        $result = $this->db->Query ($query);
        $count = $this->db->FetchRow ($result);
        return $count[0];
    }



    // Total number of votes method
    public function GetCount() {
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $this->video_id";
        $result = $this->db->Query ($query);
        $count = $this->db->FetchRow ($result);
        return $count[0];
    }



    // Total number of votes method
    public function GetCountText() {
        
        // Helpful
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $this->video_id AND rating = 1";
        $result = $this->db->Query ($query);
        $count1 = $this->db->FetchRow ($result);
        
        // Not Helpful
        $query = "SELECT COUNT(rating_id) FROM ratings WHERE video_id = $this->video_id AND rating = 0";
        $result = $this->db->Query ($query);
        $count2 = $this->db->FetchRow ($result);
        
        return '<span class="green-text">' . Language::GetText('like') . ' (' . $count1[0] . '+)</span> / <span class="red-text">' . Language::GetText('dislike') . ' (' . $count2[0] . '-)</span>';
        
    }



    // Get average rating (LEGACY uses, rated on 5 point scale)
    public function GetRating() {
        if ($this->GetCount() == 0) return 0;
        $vote_total = $this->GetLikeCount()*5;
        $average = $vote_total/$this->GetCount();
        return floor($average);
    }

}

?>