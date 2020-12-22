<?php

namespace KrokoImport\Data;

class FeedOptions
{

    private $_id;
    private $_url;
    private $_title;
    private $_updateIntervalSec;
    private $_onExistsUpdate;
    private $_lastUpdateTime;

    public function __construct(string $id, string $url, string $title, int $updateIntervalSec, bool $onExistsUpdate, ?int $lastUpdateTime = NULL)
    {
        $this->_id = $id;
        $this->_url = $url;
        $this->_title = $title;
        $this->_updateIntervalSec = $updateIntervalSec;
        $this->_onExistsUpdate = $onExistsUpdate;
        $this->_title = $title;
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

    function getUpdateIntervalSec(): int
    {
        return $this->_updateIntervalSec;
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
        $left = ($lastUpdate + $this->getUpdateIntervalSec()) - time();
        return ($left < 0) ? 0 : $left;
    }

}
