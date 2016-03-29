<?php

/**
 * Email Utility Class
 *
 * @package CumulusClips
 * @subpackage Mailer
 * @copyright Copyright (c) 2011-2016 CumulusClips (http://cumulusclips.org)
 * @license http://cumulusclips.org/LICENSE.txt GPL Version 2
 */
class Mailer
{
    protected $config;
    protected $phpmailer;
    public $template;
    public $subject = '';
    public $body = '';

    /**
     * Instantiate object
     * @param object $config Site configuration settings
     * @return object Returns object of class type
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->phpmailer = new PHPMailer();

        // Retrieve "From" name and address
        $url = parse_url ($this->config->baseUrl);
        $this->phpmailer->FromName = (empty($this->config->from_name)) ? $this->config->sitename : $this->config->from_name;
        $this->phpmailer->From = (empty($this->config->from_address)) ? 'cumulusclips@' . $url['host'] : $this->config->from_address;

        // PHPMailer SMTP Connection Settings
        if ($this->config->smtp->enabled) {
            $this->phpmailer->IsSMTP();
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Host = $this->config->smtp->host;
            $this->phpmailer->Port = $this->config->smtp->port;
            $this->phpmailer->Username = $this->config->smtp->username;
            $this->phpmailer->Password = $this->config->smtp->password;
        }
    }

    /**
     * Loads and retrives an instance given email template
     * @param string $template The name of the template to retrieve
     * @return MailerTemplate Returns instances of loaded template
     * @throws Exception Thrown if template does not exist
     */
    public function getTemplate($template)
    {
        // Verify template exists
        $file = DOC_ROOT . '/cc-content/emails/' . $template . '.tpl';
        if (!file_exists($file)) {
            throw new Exception('Given email template does not exist');
        }

        // Load Message template
        $handle = fopen($file, 'r');
        $templateContent = fread($handle, filesize ($file));

        $mailerTemplate = new MailerTemplate();
        $mailerTemplate->systemName = $template;

        // Parse email template name
        $pattern = '/<!\-\-\sBegin:\sName\s\-\->(.*?)<!\-\-\sEnd:\sName\s\-\->/is';
        if (preg_match($pattern, $templateContent, $reg)) {
            $mailerTemplate->name = trim($reg[1]);
        } else {
            $mailerTemplate->name = '';
        }

        // Parse email template body
        $pattern = '/<!\-\-\sBegin:\sMessage\s\-\->(.*?)<!\-\-\sEnd:\sMessage\s\-\->/is';
        if (preg_match($pattern, $templateContent, $reg)) {
            $mailerTemplate->body = trim($reg[1]);
        } else {
            $mailerTemplate->body = '';
        }

        // Parse email template subject
        $pattern = '/<!\-\-\sBegin:\sSubject\s\-\->(.*?)<!\-\-\sEnd:\sSubject\s\-\->/is';
        if (preg_match($pattern, $templateContent, $reg)) {
            $mailerTemplate->subject = trim($reg[1]);
        } else {
            $mailerTemplate->subject = '';
        }

        return $mailerTemplate;
    }

    /**
     * Applies the template to use for an email message (body, subject, etc.)
     * @param MailerTemplate|string $template Template to apply, if string is given then that template is loaded then applied
     * @param array $replacements (optional) List of placeholder name and their values to be replaced in the template
     * @return Mailer Provides fluent interface
     */
    public function setTemplate($template, $replacements = array())
    {
        // Apply template if template system name is given
        if (!$template instanceof MailerTemplate) {
            $template = $this->getTemplate($template);
        }

        // Retrieve custom message subject
        $textMapper = new TextMapper();
        $customSubject = $textMapper->getByCustom(array(
            'type' => TextMapper::TYPE_SUBJECT,
            'language' => 'english',
            'name' => $template->systemName
        ));

        // Set message subject
        if ($customSubject) {
            $this->subject = $customSubject->content;
        } else {
            $this->subject = $template->subject;
        }

        // Retrieve custom message body
        $customBody = $textMapper->getByCustom(array(
            'type' => TextMapper::TYPE_EMAIL_TEXT,
            'language' => 'english',
            'name' => $template->systemName
        ));

        // Set message body
        if ($customBody) {
            $this->body = $customBody->content;
        } else {
            $this->body = $template->body;
        }

        // Perform replacements if neccessary
        if (!empty($replacements)) {
            foreach ($replacements as $key => $value) {
                $search = '{' . $key . '}';
                $this->subject = str_replace($search, $value, $this->subject);
                $this->body = str_replace($search, $value, $this->body);
            }
        }

        return $this;
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
