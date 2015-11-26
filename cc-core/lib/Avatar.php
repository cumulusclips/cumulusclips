<?php

class Avatar
{
    /**
     * Save an uploaded image to a new location
     * @param string $original_file Path to the temporary image
     * @param string $original_extension Original file extension of temporary image
     * @param string $save_as Filename to save the new image as
     * @return void Temporary image is resampled, resized, and saved in its final location
     */
    public static function saveAvatar($original_file, $original_extension, $save_as)
    {
        $avatar_size = 100;
        $save_as = UPLOAD_PATH . '/avatars/' . $save_as;
        list($width_src, $height_src) = getimagesize($original_file);

        // Determine new image dimensions
        $ratio = $width_src / $height_src;

        // Check for dimension overage
        if ($width_src > $height_src && $width_src > $avatar_size) {
            $width_dst = $avatar_size;   // Resize width
            $height_dst = floor($width_dst / $ratio); // Resize height based on ratio

        } else  if ($width_src < $height_src && $height_src > $avatar_size) {
            $height_dst = $avatar_size;  // Resize height
            $width_dst = floor($height_dst * $ratio); // Resize width based on ratio

        } else if ($width_src == $height_src && $width_src > $avatar_size) {
            $width_dst = $avatar_size;  // Resize width
            $height_dst = $avatar_size;  // Resize height

        } else {
            $width_dst = $width_src;
            $height_dst = $height_src;
        }

        // Determin which type of image object to create (and how to process it) based on file extension
        if (in_array($original_extension, array('jpg', 'jpeg'))) {

            // Create image object from original image
            $image = imagecreatefromjpeg($original_file);

            // Resize image & Resample (To corrupt any possible injections)
            $image_dst = imagecreatetruecolor($width_dst, $height_dst);
            imagecopyresampled($image_dst, $image, 0, 0, 0, 0, $width_dst, $height_dst, $width_src, $height_src);

            // Save image to HDD as JPG
            imagejpeg($image_dst, $save_as, 100);

        } else {

            // Create image object from original image
            if ($original_extension == 'gif') {
                // GIFs are converted to PNGs
                $image = imagecreatefromgif($original_file);
            } else {
                $image = imagecreatefrompng($original_file);
            }

            // Create empty resized image & turn off transparency
            $image_dst = imagecreatetruecolor($width_dst, $height_dst);
            imagealphablending($image_dst, false);
            imagesavealpha($image_dst, true);

            // Resize image & Resample (To corrupt any possible injections)
            imagecopyresampled($image_dst, $image, 0, 0, 0, 0, $width_dst, $height_dst, $width_src, $height_src);

            // Save image to HDD as PNG
            imagepng($image_dst, $save_as);
        }
    }

    /**
     * Generate a unique random string for an avatar filename
     * @param string $extension The file extension for the avatar
     * @return string Random avatar filename
     */
    public static function createFilename($extension)
    {
        $extension = $extension == 'gif' ? 'png' : $extension;  // GIFs are converted to PNGs
        do {
            $filename = Functions::random(20) . '.' . $extension;
            if (!file_exists(UPLOAD_PATH . '/avatars/' . $filename)) $filename_available = true;
        } while (empty($filename_available));
        return $filename;
    }

    /**
     * Delete an avatar
     * @param integer $filename Name of file to be deleted
     * @return boolean Returns true if avatar is deleted from filesystem, false otherwise
     */
    public static function delete($filename)
    {
        try {
            Filesystem::delete(UPLOAD_PATH . '/avatars/' . $filename);
            return true;
        } catch (Exception $e) {
            App::alert('Error During Avatar Removal', "Unable to delete avatar: $filename. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determines image mime type based on given extension
     * @param string $extension The file extension to find the mime type for
     * @return int|boolean Returns image mime type value if found, false otherwise
     */
    public static function getMimeTypeFromExtension($extension)
    {
        $extensionLower = strtolower($extension);
        if ($extensionLower == 'jpg' || $extensionLower == 'jpeg') {
            return IMAGETYPE_JPEG;
        } else if ($extensionLower == 'gif') {
            return IMAGETYPE_GIF;
        } else if ($extensionLower == 'png') {
            return IMAGETYPE_PNG;
        } else {
            return false;
        }
    }
}