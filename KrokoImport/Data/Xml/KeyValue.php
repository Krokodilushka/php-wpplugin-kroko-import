<?php

namespace KrokoImport\Data\XML;

class KeyValue
{

    private $_key;
    private $_value;

    public function __construct(string $key, string $value)
    {
        $this->_key = $key;
        $this->_value = $value;
    }

    public function getKey(): string
    {
        return $this->_key;
    }

    public function getValue(): string
    {
        return $this->_value;
    }

}