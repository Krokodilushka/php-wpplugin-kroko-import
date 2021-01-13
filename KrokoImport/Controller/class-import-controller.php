<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;
use KrokoImport\Model\Holder;
use KrokoImport\Model\Import\Import;

class Import_Controller extends Controller
{
    private $_import;

    public function __construct(Holder $holder)
    {
        parent::__construct($holder);
        $this->_import = new Import;
    }

    public function manual(): string
    {
        $feedId = filter_input(INPUT_GET, 'feed_id');
        if (is_null($feedId)) {
            throw new Exception('$feedId not found');
        }
        $feed = $this->getHolder()->getFeedStorage()->get($feedId);
        $this->_import->process_feed($feed);
        $this->getHolder()->getFeedStorage()->setLastUpdateTime($feedId);
        $logs = $this->_import->get_logs();
        return '<pre>' . print_r($logs, true) . '</pre>';
    }
}