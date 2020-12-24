<?php


namespace KrokoImport\Model;


class Holder
{
    protected $_view;
    protected $_feedStorage;

    public function __construct()
    {
        $this->_view = new View(__DIR__ . '/../View');
        $this->_feedStorage = new FeedStorage();
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->_view;
    }

    /**
     * @return FeedStorage
     */
    public function getFeedStorage(): FeedStorage
    {
        return $this->_feedStorage;
    }

}