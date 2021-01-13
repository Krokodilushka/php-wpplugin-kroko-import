<?php

use KrokoImport\View\Tables\Feeds_Posts_Table;

$table = new Feeds_Posts_Table();
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