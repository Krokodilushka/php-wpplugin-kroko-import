<?php

namespace KrokoImport\Data\XML;

class Post
{

    private $_id;
    private $_title;
    private $_slug;
    private $_content;
    private $_thumbnail;
    private $_date;
    private $_categories;
    private $_metas;
    private $_tags;
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
    )
    {
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

    function get_id(): string
    {
        return $this->_id;
    }

    function get_title(): string
    {
        return $this->_title;
    }

    function get_slug(): ?string
    {
        return $this->_slug;
    }

    function get_content(): ?string
    {
        return $this->_content;
    }

    function get_thumbnail(): ?string
    {
        return $this->_thumbnail;
    }

    function get_date(): \DateTime
    {
        return $this->_date;
    }

    function get_categories(): Key_Value_Storage
    {
        return $this->_categories;
    }

    function get_metas(): Key_Value_Storage
    {
        return $this->_metas;
    }

    function get_tags(): Strings_Storage
    {
        return $this->_tags;
    }

    function get_comments(): Comments
    {
        return $this->_comments;
    }

}
