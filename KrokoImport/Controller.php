<?php

namespace KrokoImport;

class Controller {

    const CRON_UPDATE_GET_KEY_NAME = 'kroko_import_update';
    const CRON_UPDATE_MAGIC_KEY = 'kroko_import_magic';
    const CRON_MAX_FEED_UPDATE_AT_ONCE = 1;

    private static $_instance = NULL;
    private $_view;
    private $_feedStorage;
    private $_importPosts;

    private function __construct() {
        $this->_view = new View(__DIR__ . '/View');
        $this->_feedStorage = new FeedStorage();
        $this->_importPosts = new ImportPosts();
    }

    /**
     * просто страницы
     */
    private function options() {
        echo $this->_view->get('Options', array(
            'currentUrl' => self::getRequestURI(),
            'feeds' => $this->_feedStorage->getAll(),
            'magicKeyGETKey' => self::CRON_UPDATE_GET_KEY_NAME,
            'magicKey' => self::getCronMagicKey(),
        ));
    }

    private function feedOptionsUpdate($feedID) {
        $alerts = array();
        $feed = $this->_feedStorage->get($feedID);
        try {
            $feedData = XMLParser::parse(XMLParser::load($feed->getUrl()));
        } catch (\KrokoImport\Exceptions\XML $e) {
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

    private function dropFeeds() {
        $this->_feedStorage->clearDB();
    }

    private function feedOptionsInsert($url) {
        $alerts = array();
        try {
            $feedData = XMLParser::parse(XMLParser::load($url));
        } catch (\KrokoImport\Exceptions\XMLParser $e) {
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

    private function updatePosts($feedID) {
        $feed = $this->_feedStorage->get($feedID);
        try {
            $this->_importPosts->perform($feed);
            self::getInstance()->_feedStorage->setLastUpdateTime($feed->getID(), time());
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

    private function feedOptionsOnInsertOrUpdate($feedID, $feedUrl, $feedTitle, $intervalMin, $onExistsUpdate) {
        $alerts = array();
        try {
            $feedData = XMLParser::parse(XMLParser::load($feedUrl));
        } catch (\KrokoImport\Exceptions\XMLParser $e) {
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

    private function feedOptionsOnDelete($feedID) {
        $this->_feedStorage->delete($feedID);
        $this->options();
    }

    /*
     * Static
     */

    static function getRequestURI() {
        return strtok($_SERVER['REQUEST_URI'], "?") . "?" . strtok("?");
    }

    // все действия распределяются отсюда
    static function controller() {
        try {
            // create page
            $newXml = filter_input(INPUT_POST, 'new_xml');
            $xmlUrl = filter_input(INPUT_POST, 'xml_url');
            if ($newXml !== NULL && $xmlUrl !== NULL) {
                self::getInstance()->feedOptionsInsert($xmlUrl);
            } else {
                // insert, update, delete
                $getFeedID = filter_input(INPUT_GET, 'feed_id');
                $getDropFeeds = filter_input(INPUT_GET, 'drop_feeds');
                $getUpdateFeedID = filter_input(INPUT_GET, 'update_posts');
                $postDeleteFeed = filter_input(INPUT_POST, 'delete_feed', FILTER_VALIDATE_BOOLEAN);
                $updateFeedSettings = filter_input(INPUT_POST, 'update_feed_settings', FILTER_VALIDATE_BOOLEAN);
                $feedID = filter_input(INPUT_POST, 'feed_id', FILTER_VALIDATE_INT);
                $feedUrl = filter_input(INPUT_POST, 'feed_url', FILTER_VALIDATE_URL);
                $feedTitle = filter_input(INPUT_POST, 'feed_title');
                $feedIntervalMin = filter_input(INPUT_POST, 'feed_interval_min', FILTER_VALIDATE_INT);
                $onExistsUpdate = filter_input(INPUT_POST, 'on_exists_update', FILTER_VALIDATE_BOOLEAN);
                if ($getFeedID !== NULL && $postDeleteFeed !== NULL) {
                    self::getInstance()->feedOptionsOnDelete($getFeedID);
                } else if ($updateFeedSettings !== NULL && $feedUrl !== NULL && $feedTitle !== NULL) {
                    self::getInstance()->feedOptionsOnInsertOrUpdate($feedID, $feedUrl, $feedTitle, $feedIntervalMin, $onExistsUpdate);
                } else if ($getFeedID !== NULL) {
                    self::getInstance()->feedOptionsUpdate($getFeedID);
                } else if ($getDropFeeds !== NULL) {
                    self::getInstance()->dropFeeds();
                    self::getInstance()->options();
                } else if ($getUpdateFeedID !== NULL) {
                    self::getInstance()->updatePosts($getUpdateFeedID);
                } else {
                    self::getInstance()->options();
                }
            }
        } catch (\KrokoImport\Excepions\Excepion $e) {
            self::printError($e);
        }
    }

    // это запуск cron?
    static function checkUpdatesByCron() {
        $magicKey = filter_input(INPUT_GET, self::CRON_UPDATE_GET_KEY_NAME);
        if ($magicKey == self::getCronMagicKey()) {
            echo '<pre>';
            $feeds = self::getInstance()->_feedStorage->getAll();
            if (!empty($feeds)) {
                usort($feeds, 'self::feedOptionsLeftUntilUpdateComparator');
                $i = 0;
                foreach ($feeds as $feed) {
                    echo 'обновление по фиду ID ' . $feed->getID() . ' осталось до обновления ' . round($feed->leftUntilUpdateSec() / 60) . " min \n";
                    if ($feed->leftUntilUpdateSec() == 0) {
                        try {
                            self::getInstance()->_importPosts->perform($feed);
                            echo "логи:\n";
                            print_r(self::getInstance()->_importPosts->getLogs());
                            self::getInstance()->_importPosts->clearLogs();
                        } catch (\Exception $e) {
                            echo 'ошибка ' . $e->getMessage() . "\n";
                        }
                        self::getInstance()->_feedStorage->setLastUpdateTime($feeds[0]->getID(), time());
                    }
                    $i++;
                    if ($i == self::CRON_MAX_FEED_UPDATE_AT_ONCE) {
                        break;
                    }
                }
            } else {
                echo "нет фидов\n";
            }
            echo '</pre>';
            wp_die();
        }
    }

    static function getCronMagicKey() {
        $res = get_option(self::CRON_UPDATE_MAGIC_KEY);
        if ($res === false) {
            $res = md5(rand(1, 9999999));
            update_option(self::CRON_UPDATE_MAGIC_KEY, $res);
        }
        return $res;
    }

    static function feedOptionsLeftUntilUpdateComparator($objectA, $objectB) {
        return $objectA->leftUntilUpdateSec() > $objectB->leftUntilUpdateSec();
    }

    static function getInstance(): \KrokoImport\Controller {
        if (self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
     * Private static
     */

    private static function printError($e) {
        echo $this->_view->get('Error', array(
            'message' => $e->getMessage()
        ));
    }

}
