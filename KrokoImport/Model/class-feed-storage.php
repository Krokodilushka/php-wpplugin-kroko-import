<?php

namespace KrokoImport\Model;

use KrokoImport\Data\Feed_Options;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\Feed_Not_Found_Exception;

class Feed_Storage {

	const FEEDS_LAST_ID_OPTION_KEY = 'kroko_import_last_feed_id';
	const FEEDS_OPTION_KEY = 'kroko_import_feeds';

	function get_all(): array {
		$feeds = get_option( self::FEEDS_OPTION_KEY );
		$arr   = [];
		if ( ! is_null( $feeds ) && ! empty( $feeds ) ) {
			foreach ( $feeds as $feed ) {
				$arr[] = Feed_Options::from_array( $feed );
			}
		}
		return $arr;
	}

	function get( $id ): Feed_Options {
		return $this->get_all()[ $this->get_index_by_id( $id ) ];
	}

	function get_index_by_id( $id ): int {
		$index = null;
		/** @var  $feed Feed_Options */
		foreach ( $this->get_all() as $key => $feed ) {
			if ( $feed->get_id() == $id ) {
				$index = $key;
				break;
			}
		}
		if ( $index === null ) {
			throw new Feed_Not_Found_Exception();
		}

		return $index;
	}

	function update( string $id, string $title, int $save_at_once, string $url, int $interval_sec, bool $on_exists_update ): void {
		$feeds                                  = $this->get_all();
		$feeds[ $this->get_index_by_id( $id ) ] = new Feed_Options( $id, $url, $title, $save_at_once, $interval_sec, $on_exists_update );
		$this->save( $feeds );
	}

	function insert( string $title, int $save_at_once, string $url, int $interval_sec, bool $on_exists_update ): int {
		$feeds   = $this->get_all();
		$newId   = $this->increment_last_id();
		$feeds[] = new Feed_Options( $newId, $url, $title, $save_at_once, $interval_sec, $on_exists_update );
		static::save( $feeds );

		return $newId;
	}

	function delete( string $id ): void {
		$feeds = $this->get_all();
		$index = $this->get_index_by_id( $id );
		unset( $feeds[ $index ] );
		$this->save( $feeds );
	}

	function set_last_update_time( string $id ): void {
		/** @var  $feeds */
		$feeds = $this->get_all();
		$index = $this->get_index_by_id( $id );
		$feeds[ $index ]->set_last_update_time( time() );
		$this->save( $feeds );
	}

	function get_last_id(): int {
		$res = get_option( self::FEEDS_LAST_ID_OPTION_KEY );

		return $res ?: 0;
	}

	function increment_last_id(): int {
		$id = $this->get_last_id();
		$id ++;
		$res = update_option( self::FEEDS_LAST_ID_OPTION_KEY, $id );
		if ( ! $res ) {
			throw new Exception( 'update_option error' );
		}

		return $id;
	}

	function clear_db(): bool {
		return delete_option( self::FEEDS_OPTION_KEY );
	}

	private function save( $feeds ): void {
		$arr = [];
		/** @var Feed_Options $feed */
		foreach ( $feeds as $feed ) {
			$arr[] = $feed->to_array();
		}
		if ( ! update_option( self::FEEDS_OPTION_KEY, $arr ) ) {
			throw new Exception( 'Ошибка при сохранении фидов' );
		}
	}

}
