<?php


namespace KrokoImport\Model\Import;


class Loggable
{
    protected $_logs = [];

    function __construct(array &$_logs)
    {
        $this->_logs = &$_logs;
    }
}