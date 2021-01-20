<?php

namespace KrokoImport\Controller;

use KrokoImport\Model\Holder;

class Controller {
	private $_holder;

	public function __construct( Holder $holder ) {
		$this->_holder = $holder;
	}

	protected function get_holder(): Holder {
		return $this->_holder;
	}


}
