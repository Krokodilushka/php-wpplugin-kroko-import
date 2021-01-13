<?php


namespace KrokoImport\Controller;


use KrokoImport\Constants;

class Index_Controller extends Controller
{
    public function listFeeds(): string
    {
        $cronNextTime = wp_next_scheduled(Constants::CRON_NEW_POST_HOOK_NAME, []);
        $cronExecutable = 'cd '.$_SERVER['DOCUMENT_ROOT'] . ' && ' . $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/kroko-import/cron.sh';
        return $this->getHolder()->getView()->get('view-feeds-list', array(
            'feeds' => $this->getHolder()->getFeedStorage()->getAll(),
            'magicKeyGETKey' => Constants::CRON_UPDATE_GET_KEY_NAME,
            'magicKey' => self::getCronMagicKey(),
            'cronNextTime' => $cronNextTime,
            'cronExecutable' => $cronExecutable,
        ));
    }

}