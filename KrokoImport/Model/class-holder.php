<?php


namespace KrokoImport\Model;


class Holder
{
    protected $_view;
    protected $_feed_storage;

    public function __construct()
    {
        $this->_view         = new View(__DIR__ . '/../View');
        $this->_feed_storage = new Feed_Storage();
    }

    /**
     * @return View
     */
    public function get_view(): View
    {
        return $this->_view;
    }

    /**
     * @return Feed_Storage
     */
    public function get_feed_storage(): Feed_Storage
    {
        return $this->_feed_storage;
    }

}