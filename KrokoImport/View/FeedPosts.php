<?php

use KrokoImport\View\Tables\FeedsPostsTable;

$table = new FeedsPostsTable();
if ( ! empty( $feedData ) ) {
	if ( $feedData->countPosts() > 0 ) {
		foreach ( $feedData->getPosts() as $item ) {
			$table->addItem( $item );
		}
	}
	$table->prepareItems();
}
?>
<div class="feeds-data">
	<?php $table->display(); ?>
</div>