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

    function getID(): string
    {
        return $this->_id;
    }

    function getTitle(): string
    {
        return $this->_title;
    }

    function getSlug(): ?string
    {
        return $this->_slug;
    }

    function getContent(): ?string
    {
        return $this->_content;
    }

    function getThumbnail(): ?string
    {
        return $this->_thumbnail;
    }

    function getDate(): \DateTime
    {
        return $this->_date;
    }

    function getCategories(): Key_Value_Storage
    {
        return $this->_categories;
    }

    function getMetas(): Key_Value_Storage
    {
        return $this->_metas;
    }

    function getTags(): Strings_Storage
    {
        return $this->_tags;
    }

    function getComments(): Comments
    {
        return $this->_comments;
    }

}
