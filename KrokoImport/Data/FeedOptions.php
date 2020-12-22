<?php

namespace KrokoImport\Data;

class FeedOptions {

    private $_id;
    private $_url;
    private $_title;
    private $_updateIntervalSec;
    private $_onExistsUpdate;
    private $_lastUpdateTime;

    public function __construct($id, $url, $title, $updateIntervalSec, $onExistsUpdate, $lastUpdateTime = NULL) {
        $this->_id = $id;
        $this->_url = $url;
        $this->_title = $title;
        $this->_updateIntervalSec = $updateIntervalSec;
        $this->_onExistsUpdate = $onExistsUpdate;
        $this->_title = $title;
        $this->_lastUpdateTime = $lastUpdateTime;
    }

    function getID() {
        return $this->_id;
    }

    function getUrl() {
        return $this->_url;
    }

    function getTitle() {
        return $this->_title;
    }

    function getUpdateIntervalSec() {
        return $this->_updateIntervalSec;
    }

    function getOnExistsUpdate() {
        return $this->_onExistsUpdate;
    }

    function getLastUpdateTime() {
        return $this->_lastUpdateTime;
    }

    function setLastUpdateTime($lastUpdateTime) {
        $this->_lastUpdateTime = $lastUpdateTime;
    }

    function leftUntilUpdateSec() {
        $lastUpdate = $this->getLastUpdateTime() ?: 0;
        $left = ($lastUpdate + $this->getUpdateIntervalSec()) - time();
        return ($left < 0) ? 0 : $left;
    }

}
