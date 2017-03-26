<?php

class Avatar
{
    /**
     * Save an uploaded image to a new location
     *
     * @param string $sourceFile Path to the temporary image
     * @param string $filename Unique filename for the new avatar
     * @return boolean Returns true if avatar is saved, false for failures
     */
    public static function saveAvatar($sourceFile, $filename)
    {
        $avatarSize = 100;
        $saveAs = UPLOAD_PATH . '/avatars/' . $filename . '.png';
        list($widthSrc, $heightSrc) = getimagesize($sourceFile);

        // Determine new image dimensions
        $ratio = $widthSrc / $heightSrc;

        // Check for dimension overage
        if ($widthSrc > $avatarSize && $widthSrc > $heightSrc) {
            $widthDst = $avatarSize;   // Resize width
            $heightDst = floor($widthDst / $ratio); // Resize height based on ratio

        } elseif ($heightSrc > $avatarSize && $heightSrc > $widthSrc) {
            $heightDst = $avatarSize;  // Resize height
            $widthDst = floor($heightDst * $ratio); // Resize width based on ratio

        } elseif ($widthSrc > $avatarSize && $widthSrc === $heightSrc) {
            $widthDst = $heightDst = $avatarSize;  // Resize width & height

        } else {
            $widthDst = $widthSrc;
            $heightDst = $heightSrc;
        }

        // Create image resource from source file
        $handle = fopen($sourceFile, 'r');
        $imageData = fread($handle, filesize($sourceFile));
        if (!$imageSrc = @imagecreatefromstring($imageData)) {
            return false;
        }

        // Create empty resized image & turn off transparency
        $imageDst = imagecreatetruecolor($widthDst, $heightDst);
        imagealphablending($imageDst, false);
        imagesavealpha($imageDst, true);

        // Resize image & Resample (To corrupt any possible injections)
        imagecopyresampled($imageDst, $imageSrc, 0, 0, 0, 0, $widthDst, $heightDst, $widthSrc, $heightSrc);

        // Convert image to PNG and capture into string
        ob_start();
        imagepng($imageDst, null, 0);
        $imageData = ob_get_clean();

        // Save image to system and remove temp file
        try {
            Filesystem::create($saveAs);
            Filesystem::write($saveAs, $imageData);
            Filesystem::setPermissions($saveAs, 0644);
            Filesystem::delete($sourceFile);

            return true;

        } catch (Exception $exception) {
            App::alert('Error During Avatar Upload', $exception->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique random string for an avatar filename
     *
     * @return string Random avatar filename
     */
    public static function generateFilename()
    {
        $filenameAvailable = null;
        do {
            $filename = Functions::random(20);
            if (!file_exists(UPLOAD_PATH . '/avatars/' . $filename . '.png')) $filenameAvailable = true;
        } while (empty($filenameAvailable));
        return $filename;
    }

    /**
     * Delete an avatar
     *
     * @param integer $filename Filename of avatar to be deleted
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
}