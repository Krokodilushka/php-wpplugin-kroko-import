<?php


namespace KrokoImport\Controller;


use Exception;
use KrokoImport\Model\Import\Import;
use KrokoImport\Model\XML_Parser;

class Feed_Controller extends Controller {
	public function create(): string {
		$feed_url = filter_input( INPUT_POST, 'feed_url' );

		return $this->get_holder()->get_view()->get( 'view-feed-options', array(
			'alerts'         => [],
			'xmlUrl'         => $feed_url,
			'title'          => 'Feed ' . date( 'd.m.Y H:i' ),
			'intervalMin'    => 5,
			'onExistsUpdate' => true,
			'feedData'       => XML_Parser::parse( XML_Parser::load( $feed_url ) )
		) );
	}

	public function update(): string {
		$feed_id = filter_input( INPUT_GET, 'feed_id' );
		if ( is_null( $feed_id ) ) {
			throw new Exception( '$feed_id not found' );
		}
		$feed = $this->get_holder()->get_feed_storage()->get( $feed_id );

		return $this->get_holder()->get_view()->get( 'view-feed-options', array(
			'feed_id'        => $feed->get_id(),
			'xmlUrl'         => $feed->get_url(),
			'title'          => $feed->get_title(),
			'saveAtOnce'     => $feed->get_save_at_once(),
			'intervalMin'    => $feed->get_update_interval_min(),
			'onExistsUpdate' => $feed->get_on_exists_update()
		) );
	}

	public function save(): string {
		$feed_id  = filter_input( INPUT_POST, 'feed_id' );
		$feed_url = filter_input( INPUT_POST, 'feed_url' );
		if ( is_null( $feed_url ) ) {
			throw new Exception( '$feed_url not found' );
		}
		$feed_title = filter_input( INPUT_POST, 'feed_title' );
		if ( is_null( $feed_title ) ) {
			throw new Exception( '$feed_title not found' );
		}
		$save_at_once = filter_input( INPUT_POST, 'feed_save_at_once' );
		if ( is_null( $save_at_once ) ) {
			throw new Exception( '$save_at_once not found' );
		}
		$feed_interval_min = filter_input( INPUT_POST, 'feed_interval_min' );
		if ( is_null( $feed_interval_min ) ) {
			throw new Exception( '$feed_interval_min not found' );
		}
		$on_exists_update = filter_input( INPUT_POST, 'feed_on_exists_update' ) ?? false;
		$alerts           = [];
		$url              = esc_url_raw( $feed_url );
		if ( is_null( $feed_id ) ) {
			$id = $this->get_holder()->get_feed_storage()->insert( $feed_title, $save_at_once, $url, $feed_interval_min, $on_exists_update );
			if ( $id ) {
				$alerts[] = 'Фид ID ' . $id . ' добавлен';
			} else {
				$alerts[] = 'Ошибка при получении нового ID фида';
			}
		} else {
			$this->get_holder()->get_feed_storage()->update( $feed_id, $feed_title, $save_at_once, $url, $feed_interval_min, $on_exists_update );
			$alerts[] = 'Фид ID ' . $feed_id . ' обновлен';
		}

		return $this->get_holder()->get_view()->get( 'view-feed-options', array(
			'alerts'         => $alerts,
			'feed_id'        => $feed_id,
			'xmlUrl'         => $url,
			'title'          => $feed_title,
			'saveAtOnce'     => $save_at_once,
			'intervalMin'    => $feed_interval_min,
			'onExistsUpdate' => $on_exists_update
		) );
	}

	public function delete(): string {
		$feed_id = filter_input( INPUT_GET, 'feed_id' );
		if ( is_null( $feed_id ) ) {
			throw new Exception( '$feed_id not found' );
		}
		$this->get_holder()->get_feed_storage()->delete( $feed_id );

		return 'Удалено';
	}

	public function show_posts(): string {
		$feed_url = filter_input( INPUT_GET, 'feed_url' );
		if ( is_null( $feed_url ) ) {
			throw new Exception( '$feed_url not found' );
		}

		return $this->get_holder()->get_view()->get( 'view-feed-posts', array(
			'feed_url' => $feed_url,
			'feedData' => ( new Import() )->parseUrl( $feed_url )
		) );
	}
}