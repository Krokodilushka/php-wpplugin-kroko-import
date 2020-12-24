<?php


namespace KrokoImport\Controller;


use KrokoImport\Constants;

class IndexController extends Controller
{
    public function listFeeds(): string
    {
        return $this->getHolder()->getView()->get('FeedsList', array(
            'feeds' => $this->getHolder()->getFeedStorage()->getAll(),
            'magicKeyGETKey' => Constants::CRON_UPDATE_GET_KEY_NAME,
            'magicKey' => self::getCronMagicKey(),
        ));
    }
}