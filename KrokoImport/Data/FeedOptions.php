<?php

namespace KrokoImport\Data;

class FeedOptions
{

    private $_id;
    private $_url;
    private $_title;
    private $_saveAtOnce;
    private $_updateIntervalMin;
    private $_onExistsUpdate;
    private $_lastUpdateTime;

    public function __construct(string $id, string $url, string $title, int $saveAtOnce, int $updateIntervalMin, bool $onExistsUpdate, ?int $lastUpdateTime = NULL)
    {
        $this->_id = $id;
        $this->_url = $url;
        $this->_title = $title;
        $this->_saveAtOnce = $saveAtOnce;
        $this->_updateIntervalMin = $updateIntervalMin;
        $this->_onExistsUpdate = $onExistsUpdate;
        $this->_lastUpdateTime = $lastUpdateTime;
    }

    function getID(): string
    {
        return $this->_id;
    }

    function getUrl(): string
    {
        return $this->_url;
    }

    function getTitle(): string
    {
        return $this->_title;
    }

    public function getSaveAtOnce(): int
    {
        return $this->_saveAtOnce;
    }

    function getUpdateIntervalMin(): int
    {
        return $this->_updateIntervalMin;
    }

    function getOnExistsUpdate(): bool
    {
        return $this->_onExistsUpdate;
    }

    function getLastUpdateTime(): ?int
    {
        return $this->_lastUpdateTime;
    }

    function setLastUpdateTime(int $lastUpdateTime): void
    {
        $this->_lastUpdateTime = $lastUpdateTime;
    }

    function leftUntilUpdateSec(): int
    {
        $lastUpdate = $this->getLastUpdateTime() ?: 0;
        $left = ($lastUpdate + $this->getUpdateIntervalMin()) - time();
        return ($left < 0) ? 0 : $left;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $arr): FeedOptions
    {
        $id = $arr['_id'] ?? null;
        if (is_null($id)) {
            throw new \Exception('Feed $id not found');
        }
        $url = $arr['_url'] ?? null;
        if (is_null($url)) {
            throw new \Exception('Feed $id not found');
        }
        return new self(
            $id,
            $url,
            $arr['_title'] ?? '',
            $arr['_saveAtOnce'] ?? 0,
            $arr['_updateIntervalMin'] ?? 60,
            $arr['_onExistsUpdate'] ?? false,
            $arr['_lastUpdateTime'] ?? null,
        );
    }

}
