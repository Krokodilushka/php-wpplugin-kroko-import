<?php

namespace KrokoImport\Data\XML;

class StringsStorage
{

    private $_items = array();

    function put($item)
    {
        $this->_items[] = $item;
    }

    /** @return string[] */
    function get()
    {
        return $this->_items;
    }

    /** @return string|null */
    function getByKey(string $key): ?string
    {
        $res = NULL;
        if ($this->count() > 0) {
            foreach ($this->get() as $value) {
                if ($value->getKey() == $key) {
                    $res = $value;
                    break;
                }
            }
        }
        return $res;
    }

    function count(): int
    {
        return count($this->_items);
    }

    function toString(): string
    {
        return '[' . implode(', ', $this->get()) . ']';
    }

}
