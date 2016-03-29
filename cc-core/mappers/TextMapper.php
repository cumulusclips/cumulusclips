<?php

class TextMapper extends MapperAbstract
{
    /**
     * @const TYPE_LANGUAGE Descriptor for language text type
     */
    const TYPE_LANGUAGE = 'language';

    /**
     * @const TYPE_EMAIL_HTML Descriptor for html email text type
     */
    const TYPE_EMAIL_HTML = 'email_html';

    /**
     * @const TYPE_EMAIL_TEXT Descriptor for plain-text email text type
     */
    const TYPE_EMAIL_TEXT = 'email_text';

    /**
     * @const TYPE_SUBJECT Descriptor for subject text type
     */
    const TYPE_SUBJECT = 'subject';

    /**
     * @const TABLE Main source table
     */
    const TABLE = 'text';

    /**
     * @const KEY Source table's key field
     */
    const KEY = 'text_id';

    /**
     * Maps the values from a text record to the properties in a Text data model
     * @param array $record The record from the text table
     * @return Text Returns an instance of a Text data model populated with the record's data
     */
    protected function _map($record)
    {
        $text = new Text();
        $text->textId = (int) $record['text_id'];
        $text->type = $record['type'];
        $text->language = $record['language'];
        $text->name = $record['name'];
        $text->content = $record['content'];
        return $text;
    }

    /**
     * Creates or updates a text record in the database. New record is created if no id is provided.
     * @param Text $text The text being saved
     * @return int Returns the id of the saved text record
     */
    public function save(Text $text)
    {
        $db = Registry::get('db');
        if (!empty($text->textId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . static::TABLE . ' SET';
            $query .= ' type = :type, language = :language, name = :name, content = :content';
            $query .= ' WHERE text_id = :textId';
            $bindParams = array(
                ':textId' => $text->textId,
                ':type' => $text->type,
                ':language' => $text->language,
                ':name' => $text->name,
                ':content' => $text->content
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . static::TABLE;
            $query .= ' (type, language, name, content)';
            $query .= ' VALUES (:type, :language, :name, :content)';
            $bindParams = array(
                ':type' => $text->type,
                ':language' => $text->language,
                ':name' => $text->name,
                ':content' => $text->content
            );
        }

        $db->query($query, $bindParams);
        $textId = (!empty($text->textId)) ? $text->textId : $db->lastInsertId();
        return $textId;
    }
}