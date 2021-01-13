<?php

namespace KrokoImport\Controller;

use KrokoImport\Constants;
use KrokoImport\Model\Holder;

class Controller
{
    private $_holder;

    public function __construct(Holder $holder)
    {
        $this->_holder = $holder;
    }

    protected function getHolder(): Holder
    {
        return $this->_holder;
    }

    public function updatePosts(string $feedID): void
    {
        $feed = $this->_holder->getFeedStorage()->get($feedID);
        try {
            $this->_holder->getImportPosts()->perform($feed);
            $this->_holder->getFeedStorage()->setLastUpdateTime($feed->getID());
            echo $this->_holder->getView()->get('view-update-posts', array(
                'feedID' => $feed->getID(),
                'feedURL' => $feed->getUrl(),
                'response' => $this->_holder->getImportPosts()->getLogs()
            ));
        } catch (\Exception $e) {
            echo $e;
        }
    }


    /*
     * Static
     */

    public static function getCronMagicKey(): string
    {
        $res = get_option(Constants::CRON_UPDATE_MAGIC_KEY);
        if ($res === false) {
            $res = md5(rand(1, 9999999));
            update_option(Constants::CRON_UPDATE_MAGIC_KEY, $res);
        }
        return $res;
    }

}
