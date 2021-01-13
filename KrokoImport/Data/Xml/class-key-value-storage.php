<?php

namespace KrokoImport\Data\XML;

class Key_Value_Storage
{

    private $_items = array();

    function put($item)
    {
        $this->_items[] = $item;
    }

    /** @return KeyValue[] */
    function get(): array
    {
        return $this->_items;
    }

    function getByKey(string $key): ?KeyValue
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
        $tmp = [];
        if ($this->count() > 0) {
            foreach ($this->get() as $value) {
                $tmp[] = '[' . $value->getKey() . ' => ' . $value->getValue() . ']';
            }
        }
        return implode(', ', $tmp);
    }

}
