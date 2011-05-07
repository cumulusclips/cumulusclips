<?php

$picture_size = 100;
$save_as = $_SERVER['DOCUMENT_ROOT'] . '/test.png';
$original_file = $_SERVER['DOCUMENT_ROOT'] . '/original.gif';
list ($width_src, $height_src) = getimagesize ($original_file);


        // Determine new image dimensions
        $ratio = $width_src / $height_src;


        // Check for dimension overage
        if ($width_src > $height_src && $width_src > $picture_size) {
            $width_dst = $picture_size;   // Resize width
            $height_dst = floor ($width_dst / $ratio); // Resize height based on ratio

        } else  if ($width_src < $height_src && $height_src > $picture_size) {
            $height_dst = $picture_size;  // Resize height
            $width_dst = floor ($height_dst * $ratio); // Resize width based on ratio

        } else if ($width_src == $height_src && $width_src > $picture_size) {
            $width_dst = $picture_size;  // Resize width
            $height_dst = $picture_size;  // Resize height

        } else {
            $width_dst = $width_src;
            $height_dst = $height_src;
        }





       // Create image object for original image
                $image = imagecreatefromgif ($original_file);


                // Set transparency
//                $image_dst = imagecreate ($width_dst, $height_dst);
                $image_dst = imagecreatetruecolor ($width_dst, $height_dst);
                imagealphablending ($image_dst, false);
                imagesavealpha ($image_dst, true);
//                $transparent = imagecolorallocatealpha ($image_dst, 255, 255, 255, 127);
//                imagecolortransparent ($image_dst, $transparent);
//                $white = imagecolorallocate ($image_dst, 255, 255, 255);

                // Resize image & Resample (To corrupt any possible injections)
//                imagecopyresized ($image_dst, $image, 0, 0, 0, 0, $width_dst, $height_dst, $width_src, $height_src);
                imagecopyresampled ($image_dst, $image, 0, 0, 0, 0, $width_dst, $height_dst, $width_src, $height_src);

                // Save image to HDD as GIF
                imagepng ($image_dst, $save_as);

?>