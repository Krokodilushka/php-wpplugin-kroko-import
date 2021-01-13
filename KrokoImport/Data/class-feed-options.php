<?php

namespace KrokoImport\Data;

class Feed_Options
{

    private $_id;
    private $_url;
    private $_title;
    private $_save_at_once;
    private $_update_interval_min;
    private $_on_exists_update;
    private $_last_update_time;

    public function __construct(string $id, string $url, string $title, int $save_at_once, int $update_interval_min, bool $on_exists_update, ?int $last_update_time = NULL)
    {
        $this->_id = $id;
        $this->_url = $url;
        $this->_title = $title;
        $this->_save_at_once = $save_at_once;
        $this->_update_interval_min = $update_interval_min;
        $this->_on_exists_update = $on_exists_update;
        $this->_last_update_time = $last_update_time;
    }

    function get_id(): string
    {
        return $this->_id;
    }

    function get_url(): string
    {
        return $this->_url;
    }

    function get_title(): string
    {
        return $this->_title;
    }

    public function get_save_at_once(): int
    {
        return $this->_save_at_once;
    }

    function get_update_interval_min(): int
    {
        return $this->_update_interval_min;
    }

    function get_on_exists_update(): bool
    {
        return $this->_on_exists_update;
    }

    function get_last_update_time(): ?int
    {
        return $this->_last_update_time;
    }

    function set_last_update_time(int $last_update_time): void
    {
        $this->_last_update_time = $last_update_time;
    }

    function left_until_update_sec(): int
    {
        $lastUpdate = $this->get_last_update_time() ?: 0;
        $left = ($lastUpdate + ( $this->get_update_interval_min() * 60)) - time();
        return ($left < 0) ? 0 : $left;
    }

    public function to_array(): array
    {
        return get_object_vars($this);
    }

    public static function from_array(array $arr): Feed_Options
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
