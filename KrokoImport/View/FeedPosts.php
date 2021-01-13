<?php

use KrokoImport\View\Tables\FeedsPostsTable;

$table = new FeedsPostsTable();
if ( isset( $feedData ) ) {
	if ( $feedData->countPosts() > 0 ) {
		foreach ( $feedData->getPosts() as $item ) {
			$table->addItem( $item );
		}
	}
}
?>
<div class="feeds-data">
	<?php
	$table->prepareItems();
	$table->display();
	?>
</div>