<?php

class TextService extends ServiceAbstract
{
    /**
     * Retrieves language entries for a given language
     * @param string $language System name of language to retrieve text entries for
     * @return Text[] Returns list of text entries
     */
    public function getLanguageEntries($language)
    {
        $textMapper = $this->_getMapper();
        return $textMapper->getMultipleByCustom(array(
            'type' => TextMapper::TYPE_LANGUAGE,
            'language' => $language
        ));
    }

    /**
     * Retrieve instance of Text mapper
     * @return TextMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new TextMapper();
    }
}