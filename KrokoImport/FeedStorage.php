<?php

namespace KrokoImport;

use KrokoImport\Exceptions\FeedNotFound;

class FeedStorage {

    const FEEDS_LAST_ID_OPTION_KEY = 'kroko_import_last_feed_id';
    const FEEDS_OPTION_KEY = 'kroko_import_feeds';

    function getAll() {
        $res = get_option(self::FEEDS_OPTION_KEY);
        return $res ?: array();
    }

    function get($id) {
        return $this->getAll()[$this->getIndexByID($id)];
    }

    function getIndexByID($id) {
        $index = NULL;
        foreach ($this->getAll() as $key => $feed) {
            if ($feed->getID() == $id) {
                $index = $key;
                break;
            }
        }
        if ($index === NULL) {
            throw new FeedNotFound();
        }
        return $index;
    }

    function update($id, $title, $url, $intervalSec, $onExistsUpdate) {
        $feeds = $this->getAll();
        $feeds[$this->getIndexByID($id)] = new \KrokoImport\Data\FeedOptions($id, $url, $title, $intervalSec, $onExistsUpdate);
        return $this->save($feeds);
    }

    function insert($title, $url, $intervalSec, $onExistsUpdate) {
        $feeds = $this->getAll();
        $feeds[] = new \KrokoImport\Data\FeedOptions($this->incrementLastID(), $url, $title, $intervalSec, $onExistsUpdate);
        return static::save($feeds);
    }

    function delete($id) {
        $feeds = $this->getAll();
        $index = $this->getIndexByID($id);
        unset($feeds[$index]);
        return $this->save($feeds);
    }

    function setLastUpdateTime($id, $time) {
        $feeds = $this->getAll();
        $index = $this->getIndexByID($id);
        $feeds[$index]->setLastUpdateTime(time());
        return $this->save($feeds);
    }

    function getLastID() {
        $res = get_option(self::FEEDS_LAST_ID_OPTION_KEY);
        return $res ?: 0;
    }

    function incrementLastID() {
        $id = $this->getLastID();
        $id++;
        $res = update_option(self::FEEDS_LAST_ID_OPTION_KEY, $id);
        if (!$res) {
            throw new \KrokoImport\Exceptions\Exception('update_option error');
        }
        return $id;
    }

    function clearDB() {
        return delete_option(self::FEEDS_OPTION_KEY);
    }

    private function save($feeds) {
        return update_option(self::FEEDS_OPTION_KEY, $feeds);
    }

}
