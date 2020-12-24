<?php


namespace KrokoImport\Controller;


use Exception;
use KrokoImport\Model\XMLParser;

class FeedController extends Controller
{
    public function create(): string
    {
        $feedUrl = filter_input(INPUT_POST, 'feed_url');
        return $this->getHolder()->getView()->get('FeedOptions', array(
            'alerts' => [],
            'xmlUrl' => $feedUrl,
            'title' => 'Feed ' . date('d.m.Y H:i'),
            'intervalMin' => 5,
            'onExistsUpdate' => true,
            'feedData' => XMLParser::parse(XMLParser::load($feedUrl))
        ));
    }

    public function update(): string
    {
        $feedId = filter_input(INPUT_GET, 'feed_id');
        if (is_null($feedId)) {
            throw new Exception('$feedId not found');
        }
        $feed = $this->getHolder()->getFeedStorage()->get($feedId);
        return $this->getHolder()->getView()->get('FeedOptions', array(
            'feedId' => $feed->getID(),
            'xmlUrl' => $feed->getUrl(),
            'title' => $feed->getTitle(),
            'saveAtOnce' => $feed->getSaveAtOnce(),
            'intervalMin' => $feed->getUpdateIntervalMin(),
            'onExistsUpdate' => $feed->getOnExistsUpdate(),
            'feedData' => XMLParser::parse(XMLParser::load($feed->getUrl()))
        ));
    }

    public function save(): string
    {
        $feedId = filter_input(INPUT_POST, 'feed_id');
        $feedUrl = filter_input(INPUT_POST, 'feed_url');
        if (is_null($feedUrl)) {
            throw new Exception('$feedUrl not found');
        }
        $feedTitle = filter_input(INPUT_POST, 'feed_title');
        if (is_null($feedTitle)) {
            throw new Exception('$feedTitle not found');
        }
        $saveAtOnce = filter_input(INPUT_POST, 'feed_save_at_once');
        if (is_null($saveAtOnce)) {
            throw new Exception('$saveAtOnce not found');
        }
        $feedIntervalMin = filter_input(INPUT_POST, 'feed_interval_min');
        if (is_null($feedIntervalMin)) {
            throw new Exception('$feedIntervalMin not found');
        }
        $onExistsUpdate = filter_input(INPUT_POST, 'feed_on_exists_update') ?? false;
        $alerts = [];
        $url = esc_url_raw($feedUrl);
        if (is_null($feedId)) {
            $id = $this->getHolder()->getFeedStorage()->insert($feedTitle, $saveAtOnce, $url, $feedIntervalMin, $onExistsUpdate);
            if ($id) {
                $alerts[] = 'Фид ID ' . $id . ' добавлен';
            } else {
                $alerts[] = 'Ошибка при получении нового ID фида';
            }
        } else {
            $this->getHolder()->getFeedStorage()->update($feedId, $feedTitle, $saveAtOnce, $url, $feedIntervalMin, $onExistsUpdate);
            $alerts[] = 'Фид ID ' . $feedId . ' обновлен';
        }
        return $this->getHolder()->getView()->get('FeedOptions', array(
            'alerts' => $alerts,
            'feedId' => $feedId,
            'xmlUrl' => $url,
            'title' => $feedTitle,
            'saveAtOnce' => $saveAtOnce,
            'intervalMin' => $feedIntervalMin,
            'onExistsUpdate' => $onExistsUpdate,
            'feedData' => XMLParser::parse(XMLParser::load($feedUrl))
        ));
    }

    public function delete(): string
    {
        $feedId = filter_input(INPUT_POST, 'feed_id');
        if (is_null($feedId)) {
            throw new Exception('$feedId not found');
        }
        $this->getHolder()->getFeedStorage()->delete($feedId);
        return 'Удалено';
    }
}