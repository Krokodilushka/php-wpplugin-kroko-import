<?php

namespace KrokoImport\View\Tables;

use DateTime;
use KrokoImport\Constants;
use KrokoImport\Data\Feed_Options;
use WP_List_Table;

class Feeds_Table extends WP_List_Table {

	private $data;
	private $dt1;

	public function __construct( $args = array() ) {
		$this->dt1 = new DateTime( "@0" );
		parent::__construct( $args );
	}

	public function add_item( int $id, string $name, string $url, ?int $last_update, int $left_until_update_sec ) {
		$this->data[] = [
			'id'                 => $id,
			'name'               => $name,
			'url'                => $url,
			'lastUpdate'         => $last_update,
			'leftUntilUpdateSec' => $left_until_update_sec,
		];
	}

	public function get_columns() {
		$columns = [
			'id'         => 'ID',
			'name'       => 'Имя',
			'url'        => 'Url',
			'lastUpdate' => 'Последнее обновление',
			'nextUpdate' => 'Обновление через',
		];

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = [];

		return $sortable_columns;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
			case 'url':
				return $item[ $column_name ];
			case 'lastUpdate':
				return ( ( $item['lastUpdate'] !== null ) ? date_i18n( "d.m.Y H:i:s", $item['lastUpdate'] ) : '-' );
			case 'nextUpdate':
				return $this->dt1->diff( new DateTime( "@" . $item['leftUntilUpdateSec'] ) )->format( '%a д %h:%i:%s' );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_id( array $item ) {
		$edit      = http_build_query( [
			'page'                       => $_REQUEST['page'],
			Constants::ROUTE_FEED        => true,
			Constants::ROUTE_FEED_UPDATE => true,
			'feed_id'                    => $item['id']
		] );
		$delete    = http_build_query( [
			'page'                       => $_REQUEST['page'],
			Constants::ROUTE_FEED        => true,
			Constants::ROUTE_FEED_DELETE => true,
			'feed_id'                    => $item['id']
		] );
		$showPosts = http_build_query( [
			'page'                           => $_REQUEST['page'],
			Constants::ROUTE_FEED            => true,
			Constants::ROUTE_FEED_SHOW_POSTS => true,
			'feed_url'                       => $item['url']
		] );
		$importNow = http_build_query( [
			'page'                         => $_REQUEST['page'],
			Constants::ROUTE_IMPORT        => true,
			Constants::ROUTE_IMPORT_MANUAL => true,
			'feed_id'                      => $item['id']
		] );
		$actions   = array(
			'edit'       => '<a href="?' . $edit . '">Изменить</a>',
			'delete'     => '<a href="?' . $delete . '">Удалить</a>',
			'show_posts' => '<a href="?' . $showPosts . '">Посмотреть посты</a>',
			'import_now' => '<a href="?' . $importNow . '">Обновить сейчас</a>',
		);

		return sprintf( '%1$s %2$s', $item['id'], $this->row_actions( $actions ) );
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), [], $this->get_sortable_columns() );
		$this->items           = $this->data;
	}
}