<?php

namespace KrokoImport\Data\XML;

class Comment
{

    private $_id;
    private $_author;
    private $_date;
    private $_text;
    private $_meta;
    private $_replies;

    public function __construct($id, $author, $date, $text, $meta, $replies = NULL)
    {
        $this->_id = $id;
        $this->_author = $author;
        $this->_date = $date;
        $this->_text = $text;
        $this->_meta = $meta;
        $this->_replies = $replies;
    }

    function getID(): string
    {
        return $this->_id;
    }

    function getAuthor(): string
    {
        return $this->_author;
    }

    function getDate(): \DateTime
    {
        return $this->_date;
    }

    function getText(): string
    {
        return $this->_text;
    }

    function getMeta(): KeyValueStorage
    {
        return $this->_meta;
    }

    function getReplies(): ?Comments
    {
        return $this->_replies;
    }

}