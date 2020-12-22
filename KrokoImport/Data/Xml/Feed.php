<?php

namespace KrokoImport\Data\XML;

class Feed
{

    /** @var Post[] */
    private $_posts = array();

    public function putPost($post): void
    {
        $this->_posts[] = $post;
    }

    function getPosts(): array
    {
        return $this->_posts;
    }

    function countPosts(): int
    {
        return count($this->_posts);
    }

}
