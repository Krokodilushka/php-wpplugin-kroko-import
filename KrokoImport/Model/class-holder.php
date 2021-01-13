<?php


namespace KrokoImport\Model;


class Holder
{
    protected $_view;
    protected $_feedStorage;

    public function __construct()
    {
        $this->_view = new View(__DIR__ . '/../View');
        $this->_feedStorage = new Feed_Storage();
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->_view;
    }

    /**
     * @return Feed_Storage
     */
    public function getFeedStorage(): Feed_Storage
    {
        return $this->_feedStorage;
    }

}