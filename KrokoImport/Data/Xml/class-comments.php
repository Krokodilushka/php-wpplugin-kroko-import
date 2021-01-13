<?php

namespace KrokoImport\Data\XML;

class Comments {

    private $_comments = [];

    public function put($comment_item) {
        $this->_comments[] = $comment_item;
    }

    /** @return Comment[] */
    public function get(): array
    {
        return $this->_comments;
    }

    public function count(): int
    {
        return count($this->_comments);
    }

}
