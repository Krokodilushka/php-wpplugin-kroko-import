<?php


namespace KrokoImport;


use KrokoImport\Controller\Error_Controller;
use KrokoImport\Controller\Feed_Controller;
use KrokoImport\Controller\Import_Controller;
use KrokoImport\Controller\Index_Controller;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Model\Holder;

class Route {

	public static function route() {
		try {
			$holder = new Holder;
			if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED ) ) ) {
				$feed_controller = new Feed_Controller( $holder );
				if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_CREATE ) ) ) {
					echo $feed_controller->create();
				} else if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_UPDATE ) ) ) {
					echo $feed_controller->update();
				} else if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_DELETE ) ) ) {
					echo $feed_controller->delete();
				} else if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_SHOW_POSTS ) ) ) {
					echo $feed_controller->show_posts();
				} else if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_SAVE ) ) ) {
					echo $feed_controller->save();
				} else {
					throw new \Exception( 'action not found' );
				}
			} else if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_IMPORT ) ) ) {
				$post_controller = new Import_Controller( $holder );
				if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_IMPORT_MANUAL ) ) ) {
					echo $post_controller->manual();
				} else {
					throw new \Exception( 'action not found' );
				}
			} else {
				if ( ! is_null( filter_input( INPUT_GET, Constants::ROUTE_FEED_DROP_ALL ) ) ) {
					$holder->get_feed_storage()->clear_db();
				}
				echo ( new Index_Controller( $holder ) )->list_feeds();
			}

		} catch ( Exception $e ) {
			echo ( new Error_Controller( $holder ) )->error( $e );
		}
	}

	public static function pluginUrlPath(): string {
		return admin_url( 'admin.php?page=' . Constants::PLUGIN_URL_SLUG );
	}
}