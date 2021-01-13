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

    function get_id(): string
    {
        return $this->_id;
    }

    function get_author(): string
    {
        return $this->_author;
    }

    function get_date(): \DateTime
    {
        return $this->_date;
    }

    function get_text(): string
    {
        return $this->_text;
    }

    function get_meta(): Key_Value_Storage
    {
        return $this->_meta;
    }

    function get_replies(): ?Comments
    {
        return $this->_replies;
    }

}