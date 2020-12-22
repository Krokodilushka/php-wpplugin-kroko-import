<?php

namespace KrokoImport\Data\XML;

class Comments {

    private $_comments = [];

    public function put($commentItem) {
        $this->_comments[] = $commentItem;
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
