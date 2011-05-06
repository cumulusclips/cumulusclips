<?php

class Picture {

    /**
     * Save an uploaded image to a new location
     * @param string $original_file Path to the temporary image
     * @param string $save_as Filename to save the new image as
     * @return void Temporary image is resampled, resized, and saved in its final location
     */
    static function SavePicture ($original_file, $save_as) {

        $extension = Functions::GetExtension ($save_as);
        $filename = UPLOAD_PATH . '/pictures/' . $save_as;
        list ($width, $height) = getimagesize ($original_file);

        // Determine new image dimensions
        $ratio = $width/$height;

        if ($width > $height) {

            // Check width for dimension overage
            if ($width > 125) {
                // Resize width
                $width = 125;
                // Resize height based on ratio
                $height = floor($width/$ratio);
            }

        } else {

            // Check height for dimension overage
            if ($height > 125) {
                // Resize height
                $height = 125;
                // Resize width based on ratio
                $width = floor($height*$ratio);
            }

        }

        // Save image
        switch ($extension) {

            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg ($original_file);
                // Resize image
                imagejpeg ($image, $filename, 100);
                break;

            case 'png':
                $image = imagecreatefrompng ($original_file);
                // Resize image
                imagepng ($image, $filename, 0);
                break;

            case 'gif':
                $image = imagecreatefromgif ($original_file);
                // Resize image
                imagegif ($image, $filename);
                break;

        }
    }



    /**
     * Delete a profile picture from HDD
     * @param string $picture Name of picture to be deleted
     * @return void Picture is deleted from HDD
     */
    static function Delete ($picture) {
        @unlink (UPLOAD_PATH . '/pictures/' . $filename);
    }



    /**
     * Generate a unique random string for a picture filename
     * @param string $extension The file extension for the picture
     * @return string Random picture filename
     */
    static function CreateFilename ($extension) {
        do {
            $filename = Functions::Random(20) . ".$extension";
            if (!file_exists (UPLOAD_PATH . '/pictures/' . $filename)) $filename_available = true;
        } while (empty ($filename_available));
        return $filename;
    }  

}

?>
