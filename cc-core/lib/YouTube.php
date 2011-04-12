<?php

// Construct Method - Feed YouTube video page url
// ValidateUrl Method - Is provided video valid and public?
// GetBestQualityUrl Method - Retrieve highest quality video URL
// GetBestQualityFormat Method - Retrieve highest quality video format
// DownloadVideo Method - Download highest quality video
// ListVideoUrls Method - Retrieve list of all available video URLs
// ListVideoFormats Method - Retrieve list of all available video formats




class YouTubeGrabber {

    // Object Properties
    private $video_page_url;
    public $video_page;
    private $video_locations;



    // Constructor Method
    public function __construct ($video_page_url) {
        // http://www.youtube.com/watch?v=PXsu4WaLHb8
        $this->video_page_url = $video_page_url;
        $this->video_page = $this->GetVideoPage();
        $this->video_locations = $this->GetVideoLocations();
    }



    // Get HTML Source code for provided YouTube video page URL
    private function GetVideoPage() {

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $this->video_page_url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $page = curl_exec ($ch);
        curl_close ($ch);
        return $page;

    }



   // Get video URL from YouTube video page HTML source code
    private function GetVideoLocations() {

        // Video requires age verification
        if (strstr ($this->video_page, 'please verify you are 18')) {
            return false;
        }

        // Extract video id and fmt urls
        if (!preg_match ('/("|\')video_id("|\')\s?:\s?("|\')(.*?)("|\')/i', $this->video_page, $video_id) || !preg_match ('/fmt_url_map=(.*)&(.*?)=/i', $this->video_page, $fmt_url_map)) {
            return false;
        }

        return $fmt_url_map[1];

    }



    #################################################
    /*            PUBLIC FUNCTIONS                 */
    #################################################



    // Validate provided video url
    public function ValidateUrl() {
        return ($this->video_locations) ? TRUE : FALSE;
    }



    // Get highest quality video from the fmt_url_path
    public function GetBestQualityUrl() {

        $fmt_url_path = urldecode (str_replace ('%2C', ',', urldecode ($this->video_locations)));
        $various_formtats = preg_split ('/(,)?[0-9]{1,2}[|]/', $fmt_url_path);
        return $various_formtats[1];

    }



    // Get highest quality video format from the fmt_url_path
    public function GetBestQualityFormat() {

        $fmt_url_path = urldecode (str_replace ('%2C', ',', urldecode ($this->video_locations)));
        preg_match ('/^[0-9]{1,2}(|)/', $fmt_url_path , $matches);

        // 18 = 480p MP4
        // 22 = 720p HD MP4
        // 37 = 1080p HD MP4

        if ($matches[0] == 18 || $matches[0] == 22 || $matches[0] == 37) {
            return 'mp4';
        } else {
            return 'flv';
        }

    }
    
 

    // Download and Save the specified video
    public function DownloadVideo ($save_as_filename) {

        $video_url = $this->GetBestQualityUrl();
        $video_extension = $this->GetBestQualityFormat();

        $ch = curl_init();
        $filename = $save_as_filename . '.' . $video_extension;
        $fp = fopen ($filename, 'w');

        curl_setopt ($ch, CURLOPT_URL, $video_url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt ($ch, CURLOPT_FILE, $fp);
        curl_exec ($ch);
        curl_close ($ch);
        fclose ($fp);

        if (file_exists ($filename) && filesize($filename) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

}

?>