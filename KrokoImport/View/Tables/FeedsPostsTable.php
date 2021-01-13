<?php

namespace KrokoImport\View\Tables;

use DateTime;
use KrokoImport\Data\XML\Post;
use WP_List_Table;

class FeedsPostsTable extends WP_List_Table {

	/** @var Post $data */
	private $data;
	private $dt1;

	public function __construct( $args = [] ) {
		$this->dt1 = new DateTime( "@0" );
		parent::__construct( $args );
	}

	public function addItem( Post $xmlPost ) {
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

	public function columnDefault( $item, $columnName ) {
		/** @var Post $item */
		switch ( $columnName ) {
			case 'id':
				return $item->getID();
			case 'thumbnail':
				return '<a href="' . $item->getThumbnail() . '" target="_blank"><img src="' . $item->getThumbnail() . '" style="max-width:100%"></a>';
			case 'title':
				return $item->getTitle();
			case 'content':
				return $item->getContent();
			case 'tags':
				return $item->getTags()->toString();
			case 'meta':
				return $item->getMetas()->toString();
			case 'comments':
				return $item->getComments()->count();
			case 'date':
				return $item->getDate()->format( 'd.m.Y H:i:s' );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	public function prepareItems() {
		$this->_column_headers = array( $this->getColumns(), [], $this->get_sortable_columns() );
		$this->items           = $this->data;
	}
}