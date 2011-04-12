<?php

class EmailTemplate {

    public $template;
    public $headers;



    // Constructor Method
    public function __construct ($template) {
        $file = EMAIL_PATH . $template;
        $handle = fopen($file, 'r');
        $this->template = fread($handle, filesize ($file));
        $this->headers = 'From: TechieVideos.com<' . MAIN_EMAIL . '>';
        $this->headers .= "\nReply-To: " . MAIN_EMAIL;
        $this->headers .= "\nReturn-Path: " . MAIN_EMAIL;
        $this->headers .= "\nContent-Type: text/html;";
    }



    // Replace placeholder Method
    public function Replace ($data) {
        foreach ($data as $key => $value) {
            $search = '{' . $key . '}';
            $this->template = str_replace ($search, $value, $this->template);
        }
    }
    
    
    
    // Retrieve Message Block
    public function GetBlock ($block) {

        $pattern = '<!-- Begin: ' . $block . ' -->[[:space:]].*[[:space:]]<!-- End: ' . $block . ' -->';
        if (eregi ($pattern, $this->template, $reg)) {
            $content = $reg[0];
            $content = $this->StripMarkers($block, $content);
            return $content;
        } else {
            return FALSE;
        }

    }



    // Strip Markers Method
    private function StripMarkers ($block, $content) {
        $content = eregi_replace ("<!-- Begin: $block -->[[:space:]]", '', $content);
        $content = eregi_replace ("[[:space:]]<!-- End: $block -->", '', $content);
        return $content;
    }



    // Send Email Template Method
    public function Send ($to) {
        @mail($to, $this->GetBlock('Subject'), $this->GetBlock('Message'), $this->headers);
    }

}

?>