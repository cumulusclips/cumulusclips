<?php

class RatingService extends ServiceAbstract
{
    /**
     * Delete a rating
     * @param Rating $rating Instance of rating to be deleted
     * @return void Rating is deleted from system
     */
    public function delete(Rating $rating)
    {
        $ratingMapper = $this->_getMapper();
        $ratingMapper->delete($rating->ratingId);
    }

    /**
     * Rate a video based on like / dislike
     * @param Rating $rating Instance of rating being created
     * @return boolean Returns true if rating was added, false if user has already rated video
     */
    public function rateVideo(Rating $rating)
    {
        $ratingMapper = $this->_getMapper();
        if (!$ratingMapper->getRatingByCustom(array('video_id' => $rating->videoId, 'user_id' => $rating->userId))) {
            $ratingMapper->save($rating);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve ratings for a video
     * @param integer $videoId Video to retrieve ratings for
     * @return object Returns stdClass object with properties for (dis)like
     * counts & text, and total ratings
     */
    public function getRating($videoId)
    {
        $ratingMapper = $this->_getMapper();
        $rating = new stdClass();

        // Total
        $rating->count = $ratingMapper->getRatingCount($videoId);

        // Like
        $like_count = $ratingMapper->getLikeCount($videoId);
        $rating->likes = $like_count;
        $rating->like_text = Language::GetText('like');

        // Dislike
        $dislike_count = $ratingMapper->getDislikeCount($videoId);
        $rating->dislikes = $dislike_count;
        $rating->dislike_text = Language::GetText('dislike');

        return $rating;
    }

    /**
     * (LEGACY) Get average rating for a video (rated on 5 point scale)
     * @param integer $videoId Video to retrieve rating for
     * @return integer Returns average rating (5 Highest, 0 Lowest or none)
     */
    public function getFiveScaleRating($videoId)
    {
        $ratingMapper = $this->_getMapper();
        $count = $ratingMapper->getRatingCount($videoId);
        if ($count == 0) return 0;
        $vote_total = $ratingMapper->getLikeCount($videoId)*5;
        $average = $vote_total/$count;
        return floor($average);
    }
    
    /**
     * Retrieve instance of Rating mapper
     * @return RatingMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new RatingMapper();
    }
}