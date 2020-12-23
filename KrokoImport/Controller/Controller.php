<?php

namespace KrokoImport\Controller;

use KrokoImport\Constants;
use KrokoImport\Model\FeedStorage;
use KrokoImport\Model\ImportPosts;
use KrokoImport\Model\View;
use KrokoImport\Model\XMLParser;

class Controller
{

    private $_view;
    private $_feedStorage;
    private $_importPosts;

    public function __construct()
    {
        $this->_view = new View(__DIR__ . '/../View');
        $this->_feedStorage = new FeedStorage();
        $this->_importPosts = new ImportPosts();
    }

    public function options()
    {
        echo $this->_view->get('Options', array(
            'currentUrl' => self::getRequestURI(),
            'feeds' => $this->_feedStorage->getAll(),
            'magicKeyGETKey' => Constants::CRON_UPDATE_GET_KEY_NAME,
            'magicKey' => self::getCronMagicKey(),
        ));
    }

    public function feedOptionsUpdate($feedID)
    {
        $alerts = array();
        $feed = $this->_feedStorage->get($feedID);
        try {
            $feedData = XMLParser::parse(XMLParser::load($feed->getUrl()));
        } catch (\KrokoImport\Exceptions\XMLException $e) {
            $alerts[] = 'Ошибка при разбре xml файла: ' . $e->getMessage();
        }
        echo $this->_view->get('FeedOptions', array(
            'alerts' => $alerts,
            'currentUrl' => self::getRequestURI(),
            'id' => $feed->getID(),
            'xmlUrl' => $feed->getUrl(),
            'title' => $feed->getTitle(),
            'intervalMin' => $feed->getUpdateIntervalSec() / 60,
            'onExistsUpdate' => $feed->getOnExistsUpdate(),
            'feedData' => $feedData ?? NULL
        ));
    }

    public function dropFeeds()
    {
        $this->_feedStorage->clearDB();
    }

    public function feedOptionsInsert($url)
    {
        $alerts = array();
        try {
            $feedData = XMLParser::parse(XMLParser::load($url));
        } catch (\KrokoImport\Exceptions\XMLParserException $e) {
            $alerts[] = 'Ошибка при получении xml файла.' . $e->getPrevious()->getMessage();
        }
        echo $this->_view->get('FeedOptions', array(
            'alerts' => $alerts,
            'currentUrl' => self::getRequestURI(),
            'xmlUrl' => $url,
            'title' => 'Feed ' . date('d.m.Y H:i'),
            'intervalMin' => 5,
            'onExistsUpdate' => true,
            'feedData' => $feedData ?? NULL
        ));
    }

    public function updatePosts($feedID)
    {
        $feed = $this->_feedStorage->get($feedID);
        try {
            $this->_importPosts->perform($feed);
            $this->_feedStorage->setLastUpdateTime($feed->getID(), time());
            echo $this->_view->get('UpdatePosts', array(
                'feedID' => $feed->getID(),
                'feedURL' => $feed->getUrl(),
                'response' => $this->_importPosts->getLogs()
            ));
        } catch (\Exception $e) {
            echo $e;
        }
    }

    /*
     * ввод от пользователя
     */

    public function feedOptionsOnInsertOrUpdate($feedID, $feedUrl, $feedTitle, $intervalMin, $onExistsUpdate)
    {
        $alerts = array();
        try {
            $feedData = XMLParser::parse(XMLParser::load($feedUrl));
        } catch (\KrokoImport\Exceptions\XMLParserException $e) {
            $alerts[] = 'Ошибка при получении xml файла.' . $e->getPrevious()->getMessage();
        }
        $url = esc_url_raw($feedUrl);
        $intervalSec = $intervalMin * 60;
        if ($feedID !== NULL) {
            $id = $feedID;
            if ($this->_feedStorage->update($id, $feedTitle, $url, $intervalSec, $onExistsUpdate)) {
                $alerts[] = 'Фид ID ' . $id . ' обновлен';
            } else {
                $alerts[] = 'Ошибка при обновлении фида';
            }
        } else {
            $id = $this->_feedStorage->insert($feedTitle, $url, $intervalSec, $onExistsUpdate);
            if ($id) {
                $alerts[] = 'Фид ID ' . $id . ' добавлен';
            } else {
                $alerts[] = 'Ошибка при получении нового ID фида';
            }
        }
        echo $this->_view->get('FeedOptions', array(
            'alerts' => $alerts,
            'currentUrl' => self::getRequestURI(),
            'id' => $id,
            'xmlUrl' => $url,
            'title' => $feedTitle,
            'intervalMin' => $intervalMin,
            'onExistsUpdate' => $onExistsUpdate,
            'feedData' => $feedData ?? NULL
        ));
    }

    public function feedOptionsOnDelete($feedID)
    {
        $this->_feedStorage->delete($feedID);
        $this->options();
    }

    public function error($e)
    {
        echo $this->_view->get('Error', array(
            'message' => $e->getMessage()
        ));
    }

    /*
     * Static
     */

    public function getRequestURI()
    {
        return strtok($_SERVER['REQUEST_URI'], "?") . "?" . strtok("?");
    }

    public static function getCronMagicKey()
    {
        $res = get_option(Constants::CRON_UPDATE_MAGIC_KEY);
        if ($res === false) {
            $res = md5(rand(1, 9999999));
            update_option(Constants::CRON_UPDATE_MAGIC_KEY, $res);
        }
        return $res;
    }

}
