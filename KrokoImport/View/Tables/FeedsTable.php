<?php

namespace KrokoImport\View\Tables;

use DateTime;
use KrokoImport\Constants;
use WP_List_Table;

class FeedsTable extends WP_List_Table {

	private $data;
	private $dt1;

	public function __construct( $args = array() ) {
		$this->dt1 = new DateTime( "@0" );
		parent::__construct( $args );
	}

	public function addItem( int $id, string $name, string $url, ?int $lastUpdate, int $leftUntilUpdateSec ) {
		$this->data[] = [
			'id'                 => $id,
			'name'               => $name,
			'url'                => $url,
			'lastUpdate'         => $lastUpdate,
			'leftUntilUpdateSec' => $leftUntilUpdateSec,
		];
	}

	public function getColumns() {
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
		$sortable_columns = array(
			'id'         => [ 'id', true ],
			'lastUpdate' => [ 'lastUpdate', true ],
			'nextUpdate' => [ 'nextUpdate', true ],
		);

		return $sortable_columns;
	}

	public function columnDefault( $item, $columnName ) {
		switch ( $columnName ) {
			case 'name':
			case 'url':
				return $item[ $columnName ];
			case 'lastUpdate':
				return ( ( $item['lastUpdate'] !== null ) ? date_i18n( "d.m.Y H:i:s", $item['lastUpdate'] ) : '-' );
			case 'nextUpdate':
				return $this->dt1->diff( new DateTime( "@" . $item['leftUntilUpdateSec'] ) )->format( '%a д %h:%i:%s' );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_id( $item ) {
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
			'feed_id'                        => $item['id']
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

	public function prepareItems() {
		$this->_column_headers = array( $this->getColumns(), [], $this->get_sortable_columns() );
		$this->items           = $this->data;
	}
}