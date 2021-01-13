<?php

namespace KrokoImport\Data\XML;

class Feed {

	/** @var Post[] */
	private $_posts = array();

	public function put_post( $post ): void {
		$this->_posts[] = $post;
	}

	function get_posts(): array {
		return $this->_posts;
	}

	function count_posts(): int {
		return count( $this->_posts );
	}

}
