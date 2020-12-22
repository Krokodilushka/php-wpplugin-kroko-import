<?php

namespace KrokoImport\Data\XML;

class Comments {

    /** @var Comment[] */
    private $_comments = [];

    public function put($commentItem) {
        $this->_comments[] = $commentItem;
    }

    /** @return Comment[] */
    public function get() {
        return $this->_comments;
    }

    public function count() {
        return count($this->_comments);
    }

}

class Comment {

    /** @var string */
    private $_id;

    /** @var string */
    private $_author;

    /** @var \DateTime */
    private $_date;

    /** @var string */
    private $_text;

    /** @var KeyValueStorage */
    private $_meta;

    /** @var Comments|null */
    private $_replies;

    public function __construct($id, $author, $date, $text, $meta, $replies = NULL) {
        $this->_id = $id;
        $this->_author = $author;
        $this->_date = $date;
        $this->_text = $text;
        $this->_meta = $meta;
        $this->_replies = $replies;
    }

    function getID() {
        return $this->_id;
    }

    function getAuthor() {
        return $this->_author;
    }

    function getDate() {
        return $this->_date;
    }

    function getText() {
        return $this->_text;
    }

    function getMeta() {
        return $this->_meta;
    }

    function getReplies() {
        return $this->_replies;
    }

}
