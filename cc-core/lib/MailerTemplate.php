<?php

/**
 * Mailer Template
 *
 * @package CumulusClips\Mailer
 * @subpackage Template
 * @copyright Copyright (c) 2011-2016 CumulusClips (http://cumulusclips.org)
 * @license http://cumulusclips.org/LICENSE.txt GPL Version 2
 */
class MailerTemplate
{
    /**
     * @var string Name of the template
     */
    public $name;

    /**
     * @var string System name of template
     */
    public $systemName;

    /**
     * @var string Contents of the email body
     */
    public $body;

    /**
     * @var string Contents of the email subject
     */
    public $subject;
}