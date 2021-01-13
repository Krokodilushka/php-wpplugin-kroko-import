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
        $feed_id = filter_input(INPUT_GET, 'feed_id');
        if (is_null($feed_id)) {
            throw new Exception('$feed_id not found');
        }
        $feed = $this->get_holder()->get_feed_storage()->get($feed_id);
        $this->_import->process_feed($feed);
        $this->get_holder()->get_feed_storage()->set_last_update_time($feed_id);
        $logs = $this->_import->get_logs();
        return '<pre>' . print_r($logs, true) . '</pre>';
    }
}