<?php

namespace KrokoImport\Data\XML;

class Post {

    /** @var string */
    private $_id;

    /** @var string */
    private $_title;

    /** @var string|null */
    private $_slug;

    /** @var string|null */
    private $_content;

    /** @var string|null */
    private $_thumbnail;

    /** @var \DateTime */
    private $_date;

    /** @var KeyValueStorage */
    private $_categories;

    /** @var KeyValueStorage */
    private $_metas;

    /** @var KeyValueStorage */
    private $_tags;

    /** @var Comments */
    private $_comments;

    public function __construct(
            $id,
            $title,
            $slug,
            $thumbnail,
            $date,
            $content,
            $categories,
            $metas,
            $tags,
            $comments
    ) {
        $this->_id = $id;
        $this->_title = $title;
        $this->_slug = $slug;
        $this->_thumbnail = $thumbnail;
        $this->_content = $content;
        $this->_date = $date;
        $this->_categories = $categories;
        $this->_metas = $metas;
        $this->_tags = $tags;
        $this->_comments = $comments;
    }

    function getID() {
        return $this->_id;
    }

    function getTitle() {
        return $this->_title;
    }

    function getSlug() {
        return $this->_slug;
    }

    function getContent() {
        return $this->_content;
    }

    function getThumbnail() {
        return $this->_thumbnail;
    }

    function getDate() {
        return $this->_date;
    }

    function getCategories() {
        return $this->_categories;
    }

    function getMetas() {
        return $this->_metas;
    }

    function getTags() {
        return $this->_tags;
    }

    function getComments() {
        return $this->_comments;
    }

}
