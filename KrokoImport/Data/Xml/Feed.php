<?php

namespace KrokoImport\Data\XML;

class Feed {

    /** @var Post[] */
    private $_posts = array();

    public function putPost($post) {
        $this->_posts[] = $post;
    }

    function getPosts() {
        return $this->_posts;
    }

    function countPosts() {
        return count($this->_posts);
    }

}
