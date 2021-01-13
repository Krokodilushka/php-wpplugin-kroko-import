<?php

namespace KrokoImport\Model;

use KrokoImport\Data\Feed_Options;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\Feed_Not_Found_Exception;

class Feed_Storage
{

    const FEEDS_LAST_ID_OPTION_KEY = 'kroko_import_last_feed_id';
    const FEEDS_OPTION_KEY = 'kroko_import_feeds';

    function getAll(): array
    {
        $feeds = get_option(self::FEEDS_OPTION_KEY);
        $arr = [];
        if (!is_null($feeds) && !empty($feeds)) {
            foreach ($feeds as $feed) {
                $arr[] = Feed_Options::fromArray($feed);
            }
        }
        return $arr;
    }

    function get($id): Feed_Options
    {
        return $this->getAll()[$this->getIndexByID($id)];
    }

    function getIndexByID($id): int
    {
        $index = NULL;
        foreach ($this->getAll() as $key => $feed) {
            if ($feed->getID() == $id) {
                $index = $key;
                break;
            }
        }
        if ($index === NULL) {
            throw new Feed_Not_Found_Exception();
        }
        return $index;
    }

    function update(string $id, string $title, int $saveAtOnce, string $url, int $intervalSec, bool $onExistsUpdate): void
    {
        $feeds = $this->getAll();
        $feeds[$this->getIndexByID($id)] = new Feed_Options($id, $url, $title, $saveAtOnce, $intervalSec, $onExistsUpdate);
        $this->save($feeds);
    }

    function insert(string $title, int $saveAtOnce, string $url, int $intervalSec, bool $onExistsUpdate): int
    {
        $feeds = $this->getAll();
        $newId = $this->incrementLastID();
        $feeds[] = new Feed_Options($newId, $url, $title, $saveAtOnce, $intervalSec, $onExistsUpdate);
        static::save($feeds);
        return $newId;
    }

    function delete(string $id): void
    {
        $feeds = $this->getAll();
        $index = $this->getIndexByID($id);
        unset($feeds[$index]);
        $this->save($feeds);
    }

    function setLastUpdateTime(string $id): void
    {
        $feeds = $this->getAll();
        $index = $this->getIndexByID($id);
        $feeds[$index]->setLastUpdateTime(time());
        $this->save($feeds);
    }

    function getLastID(): int
    {
        $res = get_option(self::FEEDS_LAST_ID_OPTION_KEY);
        return $res ?: 0;
    }

    function incrementLastID(): int
    {
        $id = $this->getLastID();
        $id++;
        $res = update_option(self::FEEDS_LAST_ID_OPTION_KEY, $id);
        if (!$res) {
            throw new Exception('update_option error');
        }
        return $id;
    }

    function clearDB(): bool
    {
        return delete_option(self::FEEDS_OPTION_KEY);
    }

    private function save($feeds): void
    {
        $arr = [];
        foreach ($feeds as $feed) {
            $arr[] = $feed->toArray();
        }
        if (!update_option(self::FEEDS_OPTION_KEY, $arr)) {
            throw new Exception('Ошибка при сохранении фидов');
        }
    }

}
