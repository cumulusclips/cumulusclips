<?php

class Encoding {

    // Call Encoding.com API
    static function sendRequest ($xml) {

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, "http://manage.encoding.com/");
        curl_setopt ($ch, CURLOPT_POSTFIELDS, "xml=" . urlencode ($xml));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        return curl_exec ($ch);

    }



    // Create a new encoding job
    static function CreateEncodingJob ($filename, $ext) {

        global $config;
        $raw_video = $filename . '.' . $ext;

        $xml = new SimpleXMLElement('<?xml version="1.0"?><query></query>');
        $xml->AddChild ('userid', $config->en_user);
        $xml->AddChild ('userkey', $config->en_key);
        $xml->AddChild ('action', 'AddMedia');
        $xml->AddChild ('notify', HOST . '/notify/');
//        $xml->AddChild ('source',  ENCODING_FTP . '/' . $raw_video . '?passive=yes');
        $xml->AddChild ('source',  ENCODING_FTP . '/' . $raw_video);


        // FLV Format
        $flv_format = $xml->AddChild ('format');
        $flv_format->AddChild ('output', 'flv');
        $flv_format->AddChild ('video_codec', 'libx264');
        $flv_format->AddChild ('destination', 'http://' . $config->rs_user . ':' . $config->rs_key . '@storage.cloudfiles.com/' . $config->flv_bucket . '/' . $filename . '.flv');

        // iPhone/mobile Format
        $mobile_format = $xml->AddChild ('format');
        $mobile_format->AddChild ('output', 'iphone');
        $mobile_format->AddChild ('destination', 'http://' . $config->rs_user . ':' . $config->rs_key . '@storage.cloudfiles.com/' . $config->mp4_bucket . '/' . $filename . '.mp4');

        // Thumbnail Format
        $thumb_format = $xml->AddChild ('format');
        $thumb_format->AddChild ('output', 'thumbnail');
        $thumb_format->AddChild ('time', '30');
        $thumb_format->AddChild ('width', '120');
        $thumb_format->AddChild ('height', '90');
        $thumb_format->AddChild ('destination', 'http://' . $config->rs_user . ':' . $config->rs_key . '@storage.cloudfiles.com/' . $config->thumb_bucket . '/' . $filename . '.jpg');
        
        $result_xml = self::sendRequest ($xml->AsXML());
        $result = new SimpleXMLElement ($result_xml);

        // Verify job status
        if (!empty ($result->message) && $result->message == 'Added' && !empty ($result->MediaID)) {
            return $result->MediaID;
        } else {
            return FALSE;
        }

    }



    // Retrieve duration from transcoded video
    static function GetVideoDuration ($job_id) {

        global $config;

        $xml = new SimpleXMLElement('<?xml version="1.0"?><query></query>');
        $xml->AddChild ('userid', $config->en_user);
        $xml->AddChild ('userkey', $config->en_key);
        $xml->AddChild ('action', 'GetMediaInfo');
        $xml->AddChild ('mediaid', $job_id);


        $result_xml = self::sendRequest ($xml->AsXML());
        $result = new SimpleXMLElement ($result_xml);

        // Verify duration was provided
        if (!empty ($result->duration)) {
            return $result->duration;
        } else {
            return FALSE;
        }

    }



    // Get the status of a job
    static function GetEncodingStatus ($job_id) {

        global $config;

        $xml = new SimpleXMLElement('<?xml version="1.0"?><query></query>');
        $xml->AddChild ('userid', $config->en_user);
        $xml->AddChild ('userkey', $config->en_key);
        $xml->AddChild ('action', 'GetStatus');
        $xml->AddChild ('mediaid', $job_id);

        $result_xml = self::sendRequest ($xml->AsXML());
        $result = new SimpleXMLElement ($result_xml);

        // Verify job status
        if (!empty ($result->message) && $result->message == 'Finished') {
            $return = array ('overall' => $result->status, 'flv' => $result->format[0]->status, 'mp4' => $result->format[1]->status, 'thumb' => $result->format[2]->status);
        } else {
            $return = FALSE;
        }

        return $return;

    }



    // Check if encoding job finished
    static function GetEncodingResults ($xml) {

        $error = NULL;

        // Check overall job results
        if ($xml->status == 'Finished') {

            // Check each format in encoding job
            foreach ($xml->format as $format) {
                if ($format->status != 'Finished') {
                    $error = TRUE;
                    break;
                }
            }

        } else {
            $error = TRUE;
        }

        return ($error) ? FALSE : TRUE;

    }



    // Manually call notify script
    static function CallNotifyScript ($job_id, $status) {

        $xml = new SimpleXMLElement ('<?xml version="1.0"?><result></result>');
        $xml->AddChild ('mediaid', $job_id);
        $xml->AddChild ('status', $status['overall']);

        $flv_format = $xml->AddChild ('format');
        $flv_format->AddChild ('status', $status['flv']);

        $mp4_format = $xml->AddChild ('format');
        $mp4_format->AddChild ('status', $status['mp4']);

        $thumb_format = $xml->AddChild ('format');
        $thumb_format->AddChild ('status', $status['thumb']);

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, HOST . '/notify/');
        curl_setopt ($ch, CURLOPT_POSTFIELDS, "xml=" . urlencode ($xml->AsXML()));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        $buffer = curl_exec ($ch);

    }

}

?>