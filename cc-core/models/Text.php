<?php

class Text extends Model
{
    /**
     * @var int Id of text entry
     */
    public $textId;

    /**
     * @var string The type of the text entry
     */
    public $type;

    /**
     * @var string System name of the language the text entry is in
     */
    public $language;

    /**
     * @var string Identifier name for the text entry
     */
    public $name;

    /**
     * @var string Content of the text entry
     */
    public $content;
}