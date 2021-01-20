<?php

namespace KrokoImport\View\Tables;

use DateTime;
use KrokoImport\Constants;
use KrokoImport\Data\XML\Post;
use WP_List_Table;

class Feeds_Posts_Table extends WP_List_Table {

	/** @var Post $data */
	private $data;
	private $dt1;
	private $feed_url;

	public function __construct( string $feed_url, $args = [] ) {
		$this->feed_url = $feed_url;
		$this->dt1      = new DateTime( "@0" );
		parent::__construct( $args );
	}

	public function add_item( Post $xmlPost ) {
		$this->data[] = $xmlPost;
	}

	public function get_columns() {
		$columns = [
			'id'        => 'ID',
			'thumbnail' => 'Картинка',
			'title'     => 'Название',
			'content'   => 'Содержание',
			'tags'      => 'Теги',
			'meta'      => 'Мета',
			'comments'  => 'Комментарии',
			'date'      => 'Дата',
		];

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = [];

		return $sortable_columns;
	}

	public function column_default( $item, $column_name ) {
		/** @var Post $item */
		switch ( $column_name ) {
			case 'id':
				return $item->get_id();
			case 'thumbnail':
				return '<a href="' . $item->get_thumbnail() . '" target="_blank"><img src="' . $item->get_thumbnail() . '" style="max-width:100%"></a>';
			case 'title':
				return $item->get_title();
			case 'content':
				return $item->get_content();
			case 'tags':
				return $item->get_tags()->toString();
			case 'meta':
				return $item->get_metas()->toString();
			case 'comments':
				return $item->get_comments()->count();
			case 'date':
				return $item->get_date()->format( 'd.m.Y H:i:s' );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_id( Post $item ) {
		$edit    = http_build_query( [
			'page'                       => $_REQUEST['page'],
			Constants::ROUTE_IMPORT        => true,
			Constants::ROUTE_IMPORT_POST => true,
			'feed_url'                   => $this->feed_url,
			'post_id'                    => $item->get_id()
		] );
		$actions = array(
			'import_post' => '<a href="?' . $edit . '">Обновить / добавить пост</a>',
		);

		return sprintf( '%1$s %2$s', $item->get_id(), $this->row_actions( $actions ) );
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), [], $this->get_sortable_columns() );
		$this->items           = $this->data;
	}
}