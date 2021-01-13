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

    protected function get_holder(): Holder
    {
        return $this->_holder;
    }

    public function update_posts(string $feed_id): void
    {
        $feed = $this->_holder->get_feed_storage()->get($feed_id);
        try {
            $this->_holder->getImportPosts()->perform($feed);
            $this->_holder->get_feed_storage()->set_last_update_time($feed->get_id());
            echo $this->_holder->get_view()->get('view-update-posts', array(
                'feedID' => $feed->get_id(),
                'feedURL' => $feed->get_url(),
                'response' => $this->_holder->getImportPosts()->getLogs()
            ));
        } catch (\Exception $e) {
            echo $e;
        }
    }


    /*
     * Static
     */

    public static function get_cron_magic_key(): string
    {
        $res = get_option(Constants::CRON_UPDATE_MAGIC_KEY);
        if ($res === false) {
            $res = md5(rand(1, 9999999));
            update_option(Constants::CRON_UPDATE_MAGIC_KEY, $res);
        }
        return $res;
    }

}
