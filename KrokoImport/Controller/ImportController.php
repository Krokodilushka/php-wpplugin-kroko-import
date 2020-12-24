<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;
use KrokoImport\Model\Holder;
use KrokoImport\Model\ImportPosts;

class ImportController extends Controller
{
    private $_importPosts;

    public function __construct(Holder $holder)
    {
        parent::__construct($holder);
        $this->_importPosts = new ImportPosts;
    }

    public function manual(): string
    {
        $feedId = filter_input(INPUT_GET, 'feed_id');
        if (is_null($feedId)) {
            throw new Exception('$feedId not found');
        }
        $feed = $this->getHolder()->getFeedStorage()->get($feedId);
        $this->_importPosts->perform($feed);
        $logs = $this->_importPosts->getLogs();
        return '<pre>' . print_r($logs, true) . '</pre>';
    }
}