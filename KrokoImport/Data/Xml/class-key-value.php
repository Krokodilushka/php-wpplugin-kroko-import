<?php

namespace KrokoImport\Data\XML;

class Key_Value
{

    private $_key;
    private $_value;

    public function __construct(string $key, string $value)
    {
        $this->_key = $key;
        $this->_value = $value;
    }

    public function get_key(): string
    {
        return $this->_key;
    }

    public function get_value(): string
    {
        return $this->_value;
    }

}