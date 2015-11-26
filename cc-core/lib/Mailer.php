<?php

class Mailer
{
    private $config;
    public $template;
    public $phpmailer;
    public $from_name;
    public $from_address;
    public $subject = '';
    public $body = '';

    /**
     * Instantiate object
     * @global object $config Site configuration settings
     * @return object Returns object of class type
     */
    public function __construct()
    {
        global $config;
        $this->config = $config;
        $this->phpmailer = new PHPMailer();

        // Retrieve "From" name and address
        $url = parse_url (HOST);
        $this->from_name = Settings::get('from_name');
        $this->from_address = Settings::get('from_address');

        $this->from_name = (empty($this->from_name)) ? $this->config->sitename : $this->from_name;
        $this->from_address = (empty($this->from_address)) ? 'cumulusclips@' . $url['host'] : $this->from_address;

        $this->phpmailer->FromName = $this->from_name;
        $this->phpmailer->From = $this->from_address;

        // Retrieve SMTP settings
        $smtp = json_decode(Settings::get('smtp'));
        if ($smtp->enabled) {
            // PHPMailer SMTP Connection Settings
            $this->phpmailer->IsSMTP();                         // telling the class to use SMTP
            $this->phpmailer->SMTPAuth   = true;                // enable SMTP authentication
            $this->phpmailer->Host       = $smtp->host;         // sets the SMTP server
            $this->phpmailer->Port       = $smtp->port;         // set the port for the SMTP server
            $this->phpmailer->Username   = $smtp->username;     // SMTP account username
            $this->phpmailer->Password   = $smtp->password;     // SMTP account password

        }
    }

    /**
     * Load an email template into message body & subject
     * @param string $template Name of the email template to load
     * @param array $replacements [optional] Key/value pair list of placeholders
     * and their values, to be swaped within the template
     * @return void Template is loaded into body & subject properties
     */
    public function loadTemplate($template, $replacements = array())
    {
        // Load Message template
        $file = DOC_ROOT . '/cc-content/emails/' . $template . '.tpl';
        $handle = fopen($file, 'r');
        $this->template = fread($handle, filesize ($file));

        // Perform replacements if neccessary
        if (!empty($replacements)) {
            foreach ($replacements as $key => $value) {
                $search = '{' . $key . '}';
                $this->template = str_replace($search, $value, $this->template);
            }
        }

        // Load Message content
        $this->subject = $this->_getBlock('Subject');
        $this->body = $this->_getBlock('Message');
    }

    /**
     * Retrieve specified section from an email template
     * @param string $block The name of the section to retrieve
     * @return string|boolean Returns the content of the section if it exists, boolean false otherwise
     */
    private function _getBlock($block)
    {
        $pattern = '/<!\-\-\sBegin:\s' .$block . '\s\-\->(.*?)<!\-\-\sEnd:\s' . $block . '\s\-\->/is';
        if (preg_match($pattern, $this->template, $reg)) {
            return trim($reg[1]);
        } else {
            return false;
        }
    }

    /**
     * Send the current message
     * @param string $recipient Email address to send message to
     * @return void Current message is sent to specified recipient
     */
    public function send($recipient)
    {
        $this->phpmailer->Subject = $this->subject;
        $this->phpmailer->Body = $this->body;

        $this->phpmailer->ClearAddresses();
        $this->phpmailer->AddAddress($recipient);
        $this->phpmailer->Send();
    }
}
