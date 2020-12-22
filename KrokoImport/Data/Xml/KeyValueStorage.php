<?php

namespace KrokoImport\Data\XML;

class KeyValueStorage {

    /** @var KeyValue[] */
    private $_items = array();

    function put($item) {
        $this->_items[] = $item;
    }

    /** @return KeyValue[] */
    function get(): array {
        return $this->_items;
    }

    /** @return KeyValue|nukk */
    function getByKey(string $key) {
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

    function count(): int {
        return count($this->_items);
    }

    function toString() {
        $tmp = [];
        if ($this->count() > 0) {
            foreach ($this->get() as $value) {
                $tmp[] = '[' . $value->getKey() . ' => ' . $value->getValue() . ']';
            }
        }
        return implode(', ', $tmp);
    }

}

class KeyValue {

    private $_key;
    private $_value;

    public function __construct(string $key, string $value) {
        $this->_key = $key;
        $this->_value = $value;
    }

    public function getKey(): string {
        return $this->_key;
    }

    public function getValue(): string {
        return $this->_value;
    }

}
