<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;
use KrokoImport\Model\Holder;
use KrokoImport\Model\Import\Import;

class Import_Controller extends Controller {
	private $_import;

	public function __construct( Holder $holder ) {
		parent::__construct( $holder );
		$this->_import = new Import;
	}

	public function manual(): string {
		$feed_id = filter_input( INPUT_GET, 'feed_id' );
		if ( is_null( $feed_id ) ) {
			throw new Exception( '$feed_id not found' );
		}
		$feed = $this->get_holder()->get_feed_storage()->get( $feed_id );
		$this->_import->process_feed( $feed );
		$this->get_holder()->get_feed_storage()->set_last_update_time( $feed_id );
		$logs = $this->_import->get_logs();

		return '<pre>' . print_r( $logs, true ) . '</pre>';
	}

	public function one_post(): string {
		$feed_url = filter_input( INPUT_GET, 'feed_url' );
		$post_id  = filter_input( INPUT_GET, 'post_id' );
		if ( is_null( $feed_url ) ) {
			throw new Exception( '$feed_url not found' );
		}
		if ( is_null( $post_id ) ) {
			throw new Exception( '$post_id not found' );
		}
		$posts    = $this->_import->parseUrl( $feed_url );
		$findPost = null;
		foreach ( $posts->get_posts() as $post ) {
			if ( $post->get_id() == $post_id ) {
				$findPost = $post;
				break;
			}
		}
		if ( is_null( $findPost ) ) {
			throw new \Exception( 'Пост ' . $post_id . ' не найден в фиде ' . $feed_url );
		}
		$this->_import->process_post( true, $findPost );
		$logs = $this->_import->get_logs();

		return '<pre>' . print_r( $logs, true ) . '</pre>';
	}
}