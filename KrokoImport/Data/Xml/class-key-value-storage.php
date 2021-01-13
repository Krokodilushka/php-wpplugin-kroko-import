<?php

namespace KrokoImport\Data\XML;

class Key_Value_Storage
{

    private $_items = array();

    function put($item)
    {
        $this->_items[] = $item;
    }

    /** @return Key_Value[] */
    function get(): array
    {
        return $this->_items;
    }

    function get_by_key(string $key): ?Key_Value
    {
        $res = NULL;
        if ($this->count() > 0) {
            foreach ($this->get() as $value) {
                if ($value->get_key() == $key) {
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
        $tmp = [];
        if ($this->count() > 0) {
            foreach ($this->get() as $value) {
                $tmp[] = '[' . $value->get_key() . ' => ' . $value->get_value() . ']';
            }
        }
        return implode(', ', $tmp);
    }

}
